<?php

namespace MediaWiki\Extension\TelegramNotifier;

use MediaWiki\MediaWikiServices;
use EchoEvent;

class Hooks {
	// Send welcome message for telegram autocreated users
	public static function onLocalUserCreated( $user, $autocreated ) {
		if ($autocreated) {
			$services = MediaWikiServices::getInstance();
			$mUserId = $user->getId();
			$tgUsersStore = $services->getService("TelegramUsersStore");
			$userId = $tgUsersStore->findTelegramUser($mUserId);
			if ($userId === null) {
				return;
			}
			EchoEvent::create( [
				"type" => "welcome",
				"agent" => $user,
			] );
		}
	}
}
