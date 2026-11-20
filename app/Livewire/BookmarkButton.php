<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Bookmark;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookmarkButton extends Component
{
    #[Locked]
    public int $articleId;

    public bool $saved = false;

    public function mount(int $articleId): void
    {
        $this->articleId = $articleId;
        $this->saved = auth()->check() && Bookmark::where('user_id', auth()->id())->where('article_id', $articleId)->exists();
    }

    public function toggle()
    {
        if (! auth()->check()) {
            session()->put('url.intended', url()->previous());

            return $this->redirectRoute('login');
        }

        abort_unless(Article::query()->published()->whereKey($this->articleId)->exists(), 404);

        $existing = Bookmark::where('user_id', auth()->id())->where('article_id', $this->articleId)->first();

        if ($existing) {
            $existing->delete();
            $this->saved = false;
        } else {
            Bookmark::firstOrCreate(['user_id' => auth()->id(), 'article_id' => $this->articleId]);
            $this->saved = true;
        }
    }

    public function render()
    {
        return view('livewire.bookmark-button');
    }
}
