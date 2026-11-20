<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\Permissions;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Site settings';

    protected string $view = 'filament.pages.settings';

    /** Map of form field → settings key. */
    private const FIELDS = [
        'site_name' => 'site.name',
        'site_tagline' => 'site.tagline',
        'site_description' => 'site.description',
        'contact_email' => 'site.contact_email',
        'social_facebook' => 'social.links.facebook',
        'social_x' => 'social.links.x',
        'social_instagram' => 'social.links.instagram',
        'social_youtube' => 'social.links.youtube',
        'social_tiktok' => 'social.links.tiktok',
        'social_whatsapp' => 'social.links.whatsapp',
        'seo_indexing' => 'seo.indexing_enabled',
        'seo_news_name' => 'seo.google_news_publication',
        'seo_robots_extra' => 'seo.robots_extra',
        'ads_enabled' => 'ads.enabled',
        'ads_adsense_client' => 'ads.adsense_client',
        'comments_enabled' => 'comments.enabled',
        'comments_auto_approve' => 'comments.auto_approve_trusted',
    ];

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Permissions::MANAGE_SETTINGS) ?? false;
    }

    public function mount(): void
    {
        $social = (array) Setting::get('social.links', []);
        $state = [];

        foreach (self::FIELDS as $field => $key) {
            $state[$field] = str_starts_with($key, 'social.links.')
                ? ($social[substr($key, 13)] ?? null)
                : Setting::get($key);
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Tabs::make()->tabs([
                Tab::make('General')->schema([
                    TextInput::make('site_name')->required()->maxLength(60),
                    TextInput::make('site_tagline')->maxLength(120),
                    Textarea::make('site_description')->label('Default meta description')->rows(2)->maxLength(300),
                    TextInput::make('contact_email')->email(),
                ]),
                Tab::make('Social media')->columns(2)->schema([
                    TextInput::make('social_facebook')->label('Facebook')->url(),
                    TextInput::make('social_x')->label('X (Twitter)')->url(),
                    TextInput::make('social_instagram')->label('Instagram')->url(),
                    TextInput::make('social_youtube')->label('YouTube')->url(),
                    TextInput::make('social_tiktok')->label('TikTok')->url(),
                    TextInput::make('social_whatsapp')->label('WhatsApp channel')->url(),
                ]),
                Tab::make('SEO')->schema([
                    Toggle::make('seo_indexing')->label('Allow search engines to index the site')
                        ->helperText('robots.txt also blocks indexing automatically outside production.'),
                    TextInput::make('seo_news_name')->label('Google News publication name'),
                    Textarea::make('seo_robots_extra')->label('Extra robots.txt rules')->rows(4),
                ]),
                Tab::make('Advertising')->schema([
                    Toggle::make('ads_enabled')->label('Show advertisements'),
                    TextInput::make('ads_adsense_client')->label('Google AdSense publisher ID')->placeholder('ca-pub-0000000000000000')
                        ->regex('/^ca-pub-\d{10,20}$/'),
                ]),
                Tab::make('Comments')->schema([
                    Toggle::make('comments_enabled')->label('Allow reader comments'),
                    Toggle::make('comments_auto_approve')->label('Auto-approve readers with 5+ approved comments'),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $social = [];

        foreach (self::FIELDS as $field => $key) {
            if (str_starts_with($key, 'social.links.')) {
                $social[substr($key, 13)] = $state[$field] ?: null;

                continue;
            }
            Setting::put($key, $state[$field] ?? null);
        }

        Setting::put('social.links', $social);
        Cache::forget('settings.all');

        Notification::make()->title('Settings saved')->success()->send();
    }
}
