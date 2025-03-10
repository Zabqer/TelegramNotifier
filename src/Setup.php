<?php

namespace MediaWiki\Extension\TelegramNotifier;


use EchoEvent;
use User;
use MediaWiki\Extension\Notifications\Model\Event;
use MediaWiki\MediaWikiServices;

class Setup {
	public static function onRegistration() {
		$GLOBALS["wgEchoNotifiers"]["telegram"] = ["MediaWiki\\Extension\\TelegramNotifier\\TelegramNotifier", "notifyWithTelegram"];
		$GLOBALS["wgDefaultNotifyTypeAvailability"]["telegram"] = true;
	}
}
