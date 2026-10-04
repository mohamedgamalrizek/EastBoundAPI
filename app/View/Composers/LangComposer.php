<?php

namespace App\View\Composers;

use App\Enums\Status;
use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Repositories\Language\LanguageInterface;

class LangComposer
{
    protected $repoLang;

    public function __construct(LanguageInterface $repoLang)
    {

        $this->repoLang     = $repoLang;
    }

    /**
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->with('languages', $this->repoLang->all(status: Status::ACTIVE));
    }
}
