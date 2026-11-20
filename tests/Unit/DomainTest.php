<?php

namespace Tests\Unit;

use App\Enums\TransferStatus;
use App\Models\Fixture;
use App\Models\Transfer;
use App\Models\Video;
use App\Services\Content\HtmlSanitizer;
use Tests\TestCase;

class DomainTest extends TestCase
{
    public function test_html_sanitizer_removes_scripts_and_keeps_safe_embeds(): void
    {
        $clean = (new HtmlSanitizer)->sanitize(
            '<p onclick="x()">Text <a href="javascript:alert(1)">bad</a> <a href="https://ghanafa.org">ok</a></p>'.
            '<script>alert(1)</script><iframe src="https://www.youtube.com/embed/abcdefghijk"></iframe>'.
            '<iframe src="https://evil.example/embed"></iframe><img src="https://evil.example/x.png" onerror="y()">'
        );

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('evil.example', $clean);
        $this->assertStringContainsString('https://www.youtube.com/embed/abcdefghijk', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_youtube_id_parsing(): void
    {
        $this->assertSame('dQw4w9WgXcQ', Video::parseYoutubeId('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10s'));
        $this->assertSame('dQw4w9WgXcQ', Video::parseYoutubeId('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', Video::parseYoutubeId('https://www.youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', Video::parseYoutubeId('dQw4w9WgXcQ'));
        $this->assertNull(Video::parseYoutubeId('https://vimeo.com/123'));
    }

    public function test_unverified_transfer_fees_are_never_displayed_as_figures(): void
    {
        $transfer = new Transfer(['status' => TransferStatus::Rumour, 'fee_amount' => 25000000, 'fee_currency' => 'EUR', 'fee_verified' => false]);
        $this->assertSame('Reported fee unverified', $transfer->feeLabel());

        $transfer->fee_verified = true;
        $this->assertStringContainsString('25,000,000', $transfer->feeLabel());

        $this->assertSame('Undisclosed', (new Transfer(['status' => TransferStatus::Confirmed]))->feeLabel());
    }

    public function test_fixture_result_perspective(): void
    {
        $fixture = new Fixture(['home_team_id' => 1, 'away_team_id' => 2, 'status' => 'FT', 'home_score' => 1, 'away_score' => 1, 'home_pen_score' => 4, 'away_pen_score' => 3]);

        $this->assertSame('W', $fixture->resultFor(1));
        $this->assertSame('L', $fixture->resultFor(2));

        $fixture->status = 'NS';
        $this->assertNull($fixture->resultFor(1));
    }
}
