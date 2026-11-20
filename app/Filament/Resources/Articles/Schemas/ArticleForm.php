<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Enums\InsightType;
use App\Filament\Support\MediaPicker;
use App\Models\Article;
use App\Models\Category;
use App\Models\Fixture;
use App\Models\User;
use App\Services\Content\ArticleWorkflow;
use App\Support\Permissions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $canPublish = $user?->can(Permissions::PUBLISH_ARTICLES) ?? false;
        $canFeature = $user?->can(Permissions::FEATURE_ARTICLES) ?? false;
        $canEditAny = $user?->can(Permissions::EDIT_ANY_ARTICLE) ?? false;

        return $schema
            ->columns(3)
            ->components([
                Group::make()->columnSpan(['lg' => 2])->schema([
                    Section::make('Story')->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(191)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label('URL slug')
                            ->maxLength(191)
                            ->alphaDash()
                            ->unique(Article::class, 'slug', ignoreRecord: true)
                            ->helperText('Leave blank to generate from the headline. Changing a published slug breaks existing links.')
                            ->prefix(url('/news').'/'),
                        Textarea::make('excerpt')
                            ->label('Standfirst / excerpt')
                            ->rows(2)
                            ->maxLength(500)
                            ->helperText('One or two sentences shown under the headline and on cards.'),
                        RichEditor::make('body')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('articles/inline')
                            ->fileAttachmentsVisibility('public')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList'],
                                ['table', 'attachFiles', 'horizontalRule'],
                                ['undo', 'redo'],
                            ])
                            ->extraInputAttributes(['style' => 'min-height: 24rem']),
                    ]),

                    Section::make('SportyCiti Insight components')
                        ->description('Statistics tables, comparisons, charts, formation diagrams and data-feed widgets.')
                        ->collapsible()
                        ->collapsed(fn (Get $get) => $get('type') !== ArticleType::Insight->value && $get('type') !== ArticleType::Insight)
                        ->schema([InsightBlocks::make()]),

                    Section::make('Media')->collapsible()->columns(2)->schema([
                        MediaPicker::make('featured_media_id', 'featuredMedia', 'Featured image'),
                        Select::make('video_id')->label('Embedded video')->relationship('video', 'title')->searchable()->preload(false),
                        Select::make('images')->label('Additional images (photo strip)')->relationship('images', 'title')->multiple()->searchable()
                            ->createOptionForm(MediaPicker::uploadSchema())->columnSpanFull(),
                    ]),

                    Section::make('Search & social')->collapsible()->collapsed()->columns(2)->schema([
                        TextInput::make('seo_title')->label('SEO title')->maxLength(70)->helperText('Defaults to the headline. Up to ~60 characters display in Google.'),
                        TextInput::make('canonical_url')->label('Canonical URL')->url()->maxLength(255)->helperText('Only set for syndicated content.'),
                        Textarea::make('meta_description')->rows(2)->maxLength(320)->helperText('Defaults to the excerpt. ~155 characters recommended.')->columnSpanFull(),
                        MediaPicker::make('og_media_id', 'ogMedia', 'Social sharing image (optional)'),
                        Toggle::make('noindex')->label('Hide from search engines (noindex)'),
                    ]),
                ]),

                Group::make()->columnSpan(['lg' => 1])->schema([
                    Section::make('Publishing')->schema([
                        Select::make('status')
                            ->options(fn () => collect(app(ArticleWorkflow::class)->allowedStatuses(auth()->user()))
                                ->mapWithKeys(fn (ArticleStatus $s) => [$s->value => $s->getLabel()]))
                            ->default(ArticleStatus::Draft->value)
                            ->required()
                            ->live()
                            ->helperText($canPublish ? null : 'Submit for review when your story is ready. An editor will publish it.')
                            // Authors cannot change the status of an article that is already live.
                            ->disabled(fn (?Article $record) => ! $canPublish && $record && ! in_array($record->status, [ArticleStatus::Draft, ArticleStatus::InReview], true)),
                        DateTimePicker::make('published_at')
                            ->label(fn (Get $get) => in_array($get('status'), ['scheduled', ArticleStatus::Scheduled], true) ? 'Publish at' : 'Publication date')
                            ->seconds(false)
                            ->native(false)
                            ->visible($canPublish)
                            ->required(fn (Get $get) => in_array($get('status'), ['scheduled', ArticleStatus::Scheduled], true))
                            ->helperText('Set a future time with status “Scheduled” to publish automatically.'),
                        Textarea::make('review_notes')
                            ->label('Editor’s notes to author')
                            ->rows(3)
                            ->disabled(! $canPublish)
                            ->visible(fn (?Article $record) => $canPublish || filled($record?->review_notes)),
                    ]),

                    Section::make('Classification')->schema([
                        Select::make('type')->options(ArticleType::class)->default(ArticleType::News->value)->required()->live(),
                        Select::make('insight_type')->label('Insight type')->options(InsightType::class)
                            ->visible(fn (Get $get) => in_array($get('type'), ['insight', ArticleType::Insight], true))
                            ->required(fn (Get $get) => in_array($get('type'), ['insight', ArticleType::Insight], true)),
                        Select::make('category_id')->label('Category')
                            ->options(fn () => Category::query()->whereNull('parent_id')->orderBy('position')->pluck('name', 'id'))
                            ->required()->live()->afterStateUpdated(fn (Set $set) => $set('subcategory_id', null)),
                        Select::make('subcategory_id')->label('Subcategory')
                            ->options(fn (Get $get) => Category::query()->where('parent_id', $get('category_id'))->orderBy('position')->pluck('name', 'id'))
                            ->disabled(fn (Get $get) => blank($get('category_id'))),
                        Select::make('tags')->relationship('tags', 'name')->multiple()->searchable()->preload(false)
                            ->createOptionForm([TextInput::make('name')->required()->maxLength(60)]),
                        Select::make('author_id')->label('Author')
                            ->options(fn () => User::permission(Permissions::CREATE_ARTICLES)->orderBy('name')->pluck('name', 'id')
                                ->union(User::role(Permissions::SUPER_ADMIN)->pluck('name', 'id')))
                            ->default(fn () => auth()->id())
                            ->searchable()
                            ->required()
                            ->disabled(! $canEditAny)
                            ->dehydrated($canEditAny),
                        Select::make('fixture_id')->label('Linked match')
                            ->helperText('Connects the story to a match centre and powers data-feed insight components.')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => Fixture::query()->with(['homeTeam', 'awayTeam'])
                                ->whereHas('homeTeam', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('awayTeam', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orderByDesc('kickoff_at')->limit(30)->get()
                                ->mapWithKeys(fn (Fixture $f) => [$f->id => self::fixtureLabel($f)]))
                            ->getOptionLabelUsing(fn ($value) => ($f = Fixture::with(['homeTeam', 'awayTeam'])->find($value)) ? self::fixtureLabel($f) : null),
                        Select::make('relatedArticles')->label('Related articles (manual)')
                            ->relationship('relatedArticles', 'title', fn ($query, ?Article $record) => $query->when($record, fn ($q) => $q->whereKeyNot($record->id)))
                            ->multiple()->searchable()->maxItems(6),
                    ]),

                    Section::make('Placement')->schema([
                        Toggle::make('is_featured')->label('Featured story')->visible($canFeature),
                        Toggle::make('is_breaking')->label('Breaking news (ticker)')->visible($canFeature)->live(),
                        DateTimePicker::make('breaking_until')->label('Remove from ticker at')->seconds(false)->native(false)
                            ->visible(fn (Get $get) => $canFeature && $get('is_breaking')),
                        Toggle::make('allow_comments')->default(true),
                        Toggle::make('is_sponsored')->label('Sponsored content')->live()
                            ->helperText('Sponsored articles are always labelled as such on the website.'),
                        TextInput::make('sponsor_name')->visible(fn (Get $get) => (bool) $get('is_sponsored'))->required(fn (Get $get) => (bool) $get('is_sponsored'))->maxLength(120),
                        TextInput::make('sponsor_url')->url()->visible(fn (Get $get) => (bool) $get('is_sponsored'))->maxLength(255),
                    ]),
                ]),
            ]);
    }

    public static function fixtureLabel(Fixture $fixture): string
    {
        return "{$fixture->homeTeam?->name} vs {$fixture->awayTeam?->name} — {$fixture->kickoff_at?->format('j M Y')}";
    }
}
