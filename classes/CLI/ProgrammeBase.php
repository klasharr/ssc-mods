<?php

if(!defined( 'ABSPATH' )){
	exit();
}

// @todo fix temp hard coded file
define( 'DATES' , SSC_MODS_PLUGIN_DIR.'sailing_prog_v5_2018.csv' );
define( 'ONLY_HOUSE_DUTY', true );

require_once( SSC_MODS_PLUGIN_DIR.'/classes/SSCProgramme.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/EventDTO.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/Day.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/display/FullEventsTable.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/display/EventsPage.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/mappers/SailType.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/mappers/RaceSeries.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/SailingEventForm.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/ContentParser.php' );
include_once( SSC_MODS_PLUGIN_DIR.'/classes/Programme/SailTypeFilter.php' );


$form = new SailingEventForm( new safetyTeams, new SailType );

$safetyTeams = new SafetyTeams();
$sailType    = new SailType();
$sailFilter = new SailTypeFilter( $safetyTeams, $sailType, array(), array() );


$content = file_get_contents( DATES );

$contentParser  = new ContentParser( $content, $sailType, new RaceSeries, $safetyTeams );

$eventsData = $contentParser->getData( $sailFilter );

$eventsDataFlattened = array();

foreach ( $eventsData['data'] as $date => $events ) {
	/**
	 * @var EventDTO $EventDTO
	 */
	foreach( $events as $EventDTO) {
		$eventsDataFlattened[] = $EventDTO;
	}
}

$EventDTO = false;
$linesForCSV = array();
$allduties = array();

foreach( $eventsDataFlattened as $EventDTO) {

	if( ONLY_HOUSE_DUTY && !$EventDTO->isEventForHouseDuty() ) {
		continue;
	}

	$linesForCSV[] = array(
		'date' => $EventDTO->getDate(),
		'day' => $EventDTO->weekday,
		'event' => $EventDTO->getEvent(),
		'team' => $EventDTO->getTeam(),
		'type' => $EventDTO->getTypeName(),
		'time' => $EventDTO->getTime(),
	);

	getHouseDuties($EventDTO, $allduties);

	//echo $EventDTO . "\n";

}

function getCSVHeaderRow($a){
	return implode(',', array_keys($a))."\n";
}

function getRow($a){
	return implode(',', array_values($a))."\n";

}


function getColHeadings(){

	return array(
		'Duty Date' => '', // Yes dd/mm/yy
		'Duty Time' => '',
		'Event' => '', // Yes A description of what is taking place
		'Duty Type' => '', // Yes A brief description of the duty, for example Race Officer, Results, Bar
		'Swappable' => '',
		'Reminders' => '',
		'Confirmed' => '',
		'Duty Notify' => '',
		'Duty Instructions' => '',
		'Duty DBID' => '',
		'First Name' => '',
		'Last Name' => '',
		'Member Name' => '',
		'Alloc' => '',
		'Notes' => ''
	);
}


function getHouseDuties(EventDTO $dto, & $allduties){

	$s = "If you are sailing please start as soon as you can. Swap on Dutyman if you can't make this duty and in case of problems your team lead is R...";

	//1
	$duty = getCsvRow($dto);
	$duty['Duty Type'] = 'Galley';
	$duty['Duty Instructions'] = $s;
	$allduties[] = $duty;

	//2
	$duty = getCsvRow($dto);
	$duty['Duty Type'] = 'Galley';
	$duty['Duty Instructions'] = $s;
	$allduties[] = $duty;

	//3
	$duty = getCsvRow($dto);
	$duty['Duty Type'] = 'Bar';
	$duty['Duty Instructions'] = $s;
	$allduties[] = $duty;

	//4
	$duty = getCsvRow($dto);
	$duty['Duty Type'] = 'Bar';
	$duty['Duty Instructions'] = $s;
	$allduties[] = $duty;

}



function getCsvRow(EventDTO $dto){

	$a = getColHeadings();

	$a['Duty Date'] = $dto->getDate();
	$a['Event'] = $dto->getEvent();
	$a['Duty Time'] = getDutyTime($dto);

	return $a;
}


function getDutyTime(EventDTO $dto){

	switch( $dto->getTime()) {
		case '1830';
			return '1930';
			break;

		case '1900';
			return '2000';
			break;

		case '1030':
		case '1100':
			return '1145';
			break;
		default:
			throw new Exception('Invalid time');
	}
}


class SSCModsDuties {

	private $allduties;

	public function __construct( array $allduties ) {

		$this->allduties = $allduties;

	}
	public function __invoke( $args ) {

		echo getCSVHeaderRow(getColHeadings());

		foreach ($allduties as $duty) {
			echo getRow($duty);
		}

		WP_CLI::success( 'success' );
	}
}
$instance = new SSCModsDuties( $allduties );

WP_CLI::add_command( 'foo', $instance );





