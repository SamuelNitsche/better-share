<?php

namespace SamuelNitsche\BetterShare\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Spatie\Url\Url;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Yaml\Yaml;

class ShareCommand extends Command implements SignalableCommandInterface
{
    protected $signature = 'share';

    protected $description = 'Share your project with the world!';

    protected bool $shouldQuit = false;

    public function handle(): void
    {
        [$configFile, $cleanupConfigFile] = $this->getConfigFile();
        $stopNginx = $this->startNginx($configFile);

        $publicUrl = $this->getSharedUrl();
        $this->info("Your project is now shared at: [$publicUrl]");

        $cleanupRest = $this->writeConfigs($publicUrl);

        while (true) {
            if ($this->shouldQuit) {
                break;
            }

            Sleep::for(10)->seconds();
        }

        $stopNginx();
        $cleanupConfigFile();
        $cleanupRest();
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal, false|int $previousExitCode = 0): int|false
    {
        $this->shouldQuit = true;

        return false;
    }

    public function getConfigFile(): array
    {
        $appUrl = Url::fromString(config('app.url'));
        $appHost = $appUrl->getHost();
        $appPort = $appUrl->getScheme() === 'https' ? 443 : 80;

        $config = [
            'version' => 2,
            'authtoken' => config('better-share.ngrok_auth_token'),
            'tunnels' => [
                'site' => [
                    'proto' => 'http',
                    'addr' => $appPort,
                    'schemes' => [
                        'https'
                    ],
                    'request_header' => [
                        'add' => [
                            "Host: $appHost",
                            'X-Better-Share: true'
                        ]
                    ]
                ],
            ]
        ];

        $yaml = Yaml::dump($config);
        $configFile = tempnam(sys_get_temp_dir(), 'share');
        file_put_contents($configFile, $yaml);

        $cleanupConfigFile = function () use ($configFile) {
            unlink($configFile);
        };

        return [$configFile, $cleanupConfigFile];
    }

    public function startNginx(false|string $configFile): Closure
    {
        $command = "ngrok start --all --config=$configFile";
        $ngrokProcess = Process::start($command, fn($type, $buffer) => $this->output->write($buffer));

        return function () use ($ngrokProcess) {
            try {
                $ngrokProcess->signal(SIGINT);
            } catch (\Exception $e) {
            }
        };
    }

    protected function getSharedUrl(): string
    {
        $tunnels = Http::retry(5, 1000)
            ->get('http://localhost:4040/api/tunnels')
            ->collect('tunnels');

        $siteTunnel = $tunnels->firstWhere('name', 'site');

        return $siteTunnel['public_url'];
    }

    protected function writeConfigs(string $publicUrl): Closure
    {
        file_put_contents(storage_path('sharefile'), $publicUrl);

        return function () {
            @unlink(storage_path('sharefile'));
        };
    }
}
