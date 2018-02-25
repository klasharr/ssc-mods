<?php

class ContentParser {

	/**
	 * @var string
	 */
	private $content;

	/**
	 * @var string
	 */
	private $dayFilter;

	/**
	 * @var string
	 */
	private $sailEventFilter;

	/**
	 * @var SailType
	 */
	private $sailType;

	/**
	 * @var RaceSeries
	 */
	private $raceSeries;


	/**
	 * @var SafetyTeams
	 */
	private $safetyTeams;


	/**
	 * CSVParser constructor.
	 *
	 * @param $CSVPath
	 * @param SailType $sailType
	 * @param RaceSeries $raceSeries
	 * @param SafetyTeams $safetyTeams
	 *
	 * @throws Exception
	 */
	public function __construct( $content, SailType $sailType, RaceSeries $raceSeries, SafetyTeams $safetyTeams ) {

		$this->content     = $content;
		$this->sailType    = $sailType;
		$this->raceSeries  = $raceSeries;
		$this->safetyTeams = $safetyTeams;
	}

	/**
	 * @todo add a check to $days for valid days
	 *
	 * @param $days array
	 */
	public function setDayFilter( array $days = array() ) {
		$this->dayFilter = $days;
	}
	

	/**
	 * @param SailTypeFilter $sailTypeFilter
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getData( SailTypeFilter $sailTypeFilter ) {


		$tmp = array();

		$out = array(
			'data'   => array(),
			'errors' => array(),
		);

		$dataArray = explode( "\n", $this->content );


		$line = 0;
		foreach ( $dataArray as $dataLine ) {

			if ( empty( trim( $dataLine ) ) ) {
				break;
			}

			$data = explode( ",", $dataLine );

			try {
				$tmp[] = $o = new EventDTO( $line, $data, $this->sailType, $this->raceSeries, $this->safetyTeams );
			} catch ( Exception $e ) {
				$out['errors'][] = 'CSV Error line: ' . $line . ' ' . $e->getMessage();
			}
			$line ++;
		}

		$teamsToFilterOn      = $sailTypeFilter->getTeamFilter();
		$sailEventsToFilterOn = $sailTypeFilter->getTypeFilter();

		/** @var $dto EventDTO */
		foreach ( $tmp as $i => $dto ) {

			if ( ! empty( $teamsToFilterOn ) && ! in_array( $dto->getTeam(), $teamsToFilterOn ) ) {
				continue;
			}

			if ( empty( $sailEventsToFilterOn ) ) {
				$out['data'][ $dto->getDate() ][] = $dto;
			} elseif ( in_array( $dto->getType(), $sailEventsToFilterOn ) ) {
				$out['data'][ $dto->getDate() ][] = $dto;
			}
		}

		return $out;
	}
}