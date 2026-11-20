<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request, Seo $seo): View
    {
        $user = $request->user();
        $favouriteIds = $user->favouriteCategories()->pluck('categories.id');

        $feed = $favouriteIds->isNotEmpty()
            ? Article::query()->published()->forCard()
                ->where(fn ($q) => $q->whereIn('category_id', $favouriteIds)->orWhereIn('subcategory_id', $favouriteIds))
                ->latest('published_at')->limit(12)->get()
            : collect();

        $seo->set('My SportyCiti');
        $seo->noindex = true;

        return view('account.dashboard', [
            'user' => $user,
            'feed' => $feed,
            'favourites' => $user->favouriteCategories()->get(),
            'savedCount' => $user->bookmarks()->count(),
        ]);
    }

    public function bookmarks(Request $request, Seo $seo): View
    {
        $seo->set('Saved articles');
        $seo->noindex = true;

        return view('account.bookmarks', [
            'articles' => $request->user()->bookmarkedArticles()->published()->forCard()
                ->orderByPivot('created_at', 'desc')->paginate(18),
        ]);
    }

    public function interests(Request $request, Seo $seo): View
    {
        $seo->set('My interests');
        $seo->noindex = true;

        return view('account.interests', [
            'tree' => Category::navigation(),
            'selected' => $request->user()->favouriteCategories()->pluck('categories.id')->all(),
        ]);
    }

    public function updateInterests(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'categories' => ['array', 'max:40'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')->where('is_active', true)],
        ]);

        $request->user()->favouriteCategories()->sync($data['categories'] ?? []);

        return redirect()->route('account.dashboard')->with('status', 'Your interests have been updated.');
    }

    public function profile(Request $request, Seo $seo): View
    {
        $seo->set('Profile & security');
        $seo->noindex = true;

        return view('account.profile', ['user' => $request->user()]);
    }
}
