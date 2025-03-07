<?php

use MediaWiki\MediaWikiServices;
use EchoEvent;
use MediaWiki\Extension\Notifications\Formatters\EchoEventFormatter;
use MediaWiki\Extension\Notifications\Services;
use User;
use MediaWiki\Parser\Sanitizer;

function TelegramSendMessage($token, $chatID, $message) {

	if ($token === "") {
		return json_encode(array("error" => "invalid telegram token"));
	}

	$url = "https://api.telegram.org/bot" . $token . "/sendMessage";
	$data = [
		"chat_id" => $chatID,
		"text" => $message,
		"parse_mode" => "HTML"
	];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);

	if (curl_errno($ch)) {
		$error_msg = curl_error($ch);
		curl_close($ch);
		return json_encode(array("error" => $error_msg));
	}

	$http_code_message = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code_message >= 200 && $http_code_message < 300) {
		$result = json_decode($response, true);
		if ($result["ok"]) {
			$result = json_encode(array("success" => $result["ok"]));
		} else {
			$result = json_encode(array("error" => $result));
		}
	} else {
		$result = json_encode(array("error" => "HTTP error " . $http_code_message));
	}

	curl_close($ch);
	return $result;
}


class EchoTelegramMessageFormatter extends EchoEventFormatter {
	protected function formatModel( EchoEventPresentationModel $model ) {
		$subject = Sanitizer::stripAllTags( $model->getSubjectMessage()->parse() );

		$text = Sanitizer::stripAllTags( $model->getHeaderMessage()->parse() );

		$content = "<b>" . $subject . "</b>";
		$content .= "\n";
		$content .= $text;
		$content .= "\n";
		$body = $model->getBodyMessage();
		if ($body) {
			$content .= Sanitizer::stripAllTags( $body->parse() );
			$content .= "\n";
		}
		$link = $model->getPrimaryLinkWithMarkAsRead();
		$url = wfExpandUrl( $link["url"], PROTO_CANONICAL );
		$content .= "<a href='" . $url . "'>" . $link["label"] . "</a>" ;
		return $content;

	}
}

class TelegramNotifier {
    public static function notifyWithTelegram( User $user, EchoEvent $event ) {
		$attributeManager = Services::getInstance()->getAttributeManager();
		$userTelegramNotifications = $attributeManager->getUserEnabledEvents($user, "telegram");
		if (!in_array($event->getType(), $userTelegramNotifications)) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		$mUserId = $user->getId();
		$tgUsersStore = $services->getService("TelegramUsersStore");
		$userId = $tgUsersStore->findTelegramUser($mUserId);
		if ($userId === null) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		$config = $services->getMainConfig();

		$userOptionsLookup = $services->getUserOptionsLookup();
		$lang = $services->getLanguageFactory()->getLanguage($userOptionsLookup->getOption($user, "language"));

		$formatter = new EchoTelegramMessageFormatter($user, $lang );
		$content = $formatter->format( $event, "telegram");

		TelegramSendMessage($config->get("TelegramNotifier_BotToken"), $userId, $content);
    }
}

