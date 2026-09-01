<?php

namespace App\Services\AI;

class SipanduAIContext
{
    public function __construct(private readonly SipanduAITools $tools)
    {
    }

    public function forQuestion(string $question): string
    {
        $question = mb_strtolower($question);
        $parts = [];

        if (str_contains($question, 'penduduk') || str_contains($question, 'jiwa')) {
            $parts[] = 'Total penduduk yang tersedia: '.$this->tools->getTotalPenduduk().'.';
        }

        if (str_contains($question, 'rumah') || str_contains($question, 'layak')) {
            $parts[] = 'Total rumah yang tersedia: '.$this->tools->getTotalRumah().'.';
        }

        if (str_contains($question, 'keluarga') || str_contains($question, 'kk')) {
            $parts[] = 'Total keluarga yang tersedia: '.$this->tools->getJumlahKeluarga().'.';
        }

        if ($parts === []) {
            return 'Tidak ada data statistik yang relevan ditemukan untuk pertanyaan ini.';
        }

        return implode(' ', $parts);
    }

    public function cachedForQuestion(string $question): string
    {
        return app(\Illuminate\Contracts\Cache\Repository::class)->remember('ai:context:'.sha1($question), 300, fn (): string => $this->forQuestion($question));
    }
}
