<?php

declare(strict_types=1);

namespace App\Command\Card;

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'card:generate_audio', description: 'Hello PhpStorm')]
class GenerateAudioCommand extends Command
{
	protected function execute(InputInterface $input, OutputInterface $output): int {

		$textToSpeechClient = new TextToSpeechClient();
		$languageCode = 'pl-PL'; //'ru-RU';
		//$speakingRate = (float)($input['speed'] ?? 1.0);
		//$pitch = (float)($input['pitch'] ?? 0.0);
		//$audioEncoding = $input['format'] ?? 'MP3';

		$input = new SynthesisInput();
		$input->setText('Japan\'s national soccer team won against Colombia!');
		$voice = new VoiceSelectionParams();
		$voice->setLanguageCode($languageCode);
//		$voice->setLanguageCode('en-US');
		$audioConfig = new AudioConfig();
		$audioConfig->setAudioEncoding(AudioEncoding::MP3);
		$request = (new SynthesizeSpeechRequest())
			->setInput($input)
			->setVoice($voice)
			->setAudioConfig($audioConfig);

		$resp = $textToSpeechClient->synthesizeSpeech($request);
		file_put_contents('test.mp3', $resp->getAudioContent());

		return Command::SUCCESS;
	}
}
