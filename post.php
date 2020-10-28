<?php
require_once('phpQuery-onefile.php');

class Post
{
	const ZENN_URL = 'https://zenn.dev';

	public function __construct()
	{
		$message_post = $this->getZennContent(self::ZENN_URL);
		$this->postSlack(getenv('SLACK_URL'), $message_post);
	}
	public function getZennContent($zenn_url) {
		$html = file_get_contents($zenn_url);
		$phpobj = phpQuery::newDocument($html);
		$contents = $phpobj['#tech-trend .article-list-item .al-item__content .al-item__link'];
		$link_text = "ZennのURL:" . $zenn_url . "\n\n";
		$number = 0;
		foreach ($contents as $content) {
			$number++;
			$href = pq($content)->attr('href');
			$title = pq($content)->find('.al-item__title')->text();
			$title = str_replace('<', '＜', $title);
			$title = str_replace('>', '＞', $title);
			if ($number < 10) {
				$anchor = $number . ".   " . "<" . $zenn_url . $href . "|" . $title . ">\n";
			} else {
				$anchor = $number . ". " . "<" . $zenn_url . $href . "|" . $title . ">\n";
			}
			$link_text .= $anchor;
		}
		$message = [
			'username' => 'Zennのトレンドを取得してくるよー',
			'icon_emoji' => ':slack:',
			'text' => $link_text
		];

		$message_json = json_encode($message);
		$message_post = 'payload=' . urlencode($message_json);

		return $message_post;
	}

	public function postSlack($url, $message_post) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message_post);
		curl_exec($ch);
		curl_close($ch);
	}
}
