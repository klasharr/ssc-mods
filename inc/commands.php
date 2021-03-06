<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( SSC_MODS_PLUGIN_DIR . '/classes/CLI/HouseDuties.php' );

$house_duties = new \SSCMods\HouseDuties();
WP_CLI::add_command( 'houseduties', $house_duties );

require_once( SSC_MODS_PLUGIN_DIR . '/classes/CLI/SafetyDuties.php' );

$safety_duties = new \SSCMods\SafetyDuties();
WP_CLI::add_command( 'safetyduties', $safety_duties );

require_once( SSC_MODS_PLUGIN_DIR . '/classes/CLI/Members.php' );

$members = new \SSCMods\Members();
WP_CLI::add_command( 'members', $members );

require_once( SSC_MODS_PLUGIN_DIR . '/classes/CLI/Events.php' );

$members = new \SSCMods\Events();
WP_CLI::add_command( 'events', $members );


require_once( SSC_MODS_PLUGIN_DIR . '/classes/CLI/SafetyTeamsUtil.php' );

$safetyTeams = new \SSCMods\SafetyTeamsUtil();
WP_CLI::add_command( 'safetyteams', $safetyTeams );