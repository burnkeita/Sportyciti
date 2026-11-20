<?php

namespace App\Support;

/**
 * Single source of truth for roles and permissions.
 * Run `php artisan db:seed --class=RolesAndPermissionsSeeder` after changing this file.
 */
final class Permissions
{
    public const ACCESS_ADMIN = 'access admin panel';

    public const CREATE_ARTICLES = 'create articles';

    public const EDIT_OWN_ARTICLES = 'edit own articles';

    public const EDIT_ANY_ARTICLE = 'edit any article';

    public const PUBLISH_ARTICLES = 'publish articles';

    public const DELETE_ARTICLES = 'delete articles';

    public const FEATURE_ARTICLES = 'feature articles';

    public const MANAGE_CATEGORIES = 'manage categories';

    public const MANAGE_TAGS = 'manage tags';

    public const UPLOAD_MEDIA = 'upload media';

    public const MANAGE_MEDIA = 'manage media';

    public const MODERATE_COMMENTS = 'moderate comments';

    public const MANAGE_MULTIMEDIA = 'manage multimedia';

    public const MANAGE_ADS = 'manage advertisements';

    public const MANAGE_NEWSLETTER = 'manage newsletter';

    public const MANAGE_HOMEPAGE = 'manage homepage';

    public const MANAGE_SETTINGS = 'manage settings';

    public const MANAGE_USERS = 'manage users';

    public const MANAGE_ROLES = 'manage roles';

    public const MANAGE_SPORTS_DATA = 'manage sports data';

    public const MANAGE_TRANSFERS = 'manage transfers';

    public const VIEW_AUDIT_LOGS = 'view audit logs';

    public const VIEW_ANALYTICS = 'view analytics';

    public const SUPER_ADMIN = 'Super Administrator';

    public const ADMIN = 'Administrator';

    public const EDITOR_IN_CHIEF = 'Editor-in-Chief';

    public const EDITOR = 'Editor';

    public const JOURNALIST = 'Journalist';

    public const CONTRIBUTOR = 'Contributor';

    public const MODERATOR = 'Moderator';

    /** @return list<string> */
    public static function all(): array
    {
        return array_values(array_filter(
            (new \ReflectionClass(self::class))->getConstants(),
            fn ($value, $name) => ! in_array($name, ['SUPER_ADMIN', 'ADMIN', 'EDITOR_IN_CHIEF', 'EDITOR', 'JOURNALIST', 'CONTRIBUTOR', 'MODERATOR'], true),
            ARRAY_FILTER_USE_BOTH,
        ));
    }

    /**
     * Role → permissions. The Super Administrator bypasses all checks via Gate::before
     * and is therefore not listed.
     *
     * @return array<string, list<string>>
     */
    public static function roleMap(): array
    {
        $editorial = [
            self::ACCESS_ADMIN, self::CREATE_ARTICLES, self::EDIT_OWN_ARTICLES, self::EDIT_ANY_ARTICLE,
            self::PUBLISH_ARTICLES, self::FEATURE_ARTICLES, self::MANAGE_TAGS, self::UPLOAD_MEDIA, self::MANAGE_MEDIA,
            self::MODERATE_COMMENTS, self::MANAGE_MULTIMEDIA, self::MANAGE_TRANSFERS, self::VIEW_ANALYTICS,
        ];

        return [
            self::ADMIN => array_values(array_diff(self::all(), [self::MANAGE_ROLES])),
            self::EDITOR_IN_CHIEF => [
                ...$editorial,
                self::DELETE_ARTICLES, self::MANAGE_CATEGORIES, self::MANAGE_HOMEPAGE, self::MANAGE_SPORTS_DATA,
                self::MANAGE_NEWSLETTER, self::VIEW_AUDIT_LOGS,
            ],
            self::EDITOR => $editorial,
            self::JOURNALIST => [self::ACCESS_ADMIN, self::CREATE_ARTICLES, self::EDIT_OWN_ARTICLES, self::UPLOAD_MEDIA],
            self::CONTRIBUTOR => [self::ACCESS_ADMIN, self::CREATE_ARTICLES, self::EDIT_OWN_ARTICLES],
            self::MODERATOR => [self::ACCESS_ADMIN, self::MODERATE_COMMENTS],
        ];
    }

    /** @return list<string> */
    public static function staffRoles(): array
    {
        return [self::SUPER_ADMIN, ...array_keys(self::roleMap())];
    }
}
