<?php

namespace FuryBee\LoggerUi\Http\Controllers;

use Illuminate\View\View;

class HomeController
{
    /**
     * Returns the Logger UI home page.
     *
     * @return View
     */
    public function __invoke()
    {
        return view('logger-ui::layout');
    }
}
