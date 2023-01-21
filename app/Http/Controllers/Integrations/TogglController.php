<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Services\ExistService;
use App\Services\TogglService;
use Illuminate\Http\Request;

class TogglController extends Controller
{
    private $toggl;
    private $exist;

    public function __construct(TogglService $toggl, ExistService $exist)
    {
        $this->middleware('auth');
        $this->toggl = $toggl;
        $this->exist = $exist;
    }

    /**
     * ROUTE: /services/toggl/connect
     * METHOD: POST
     */
    public function connect(Request $request)
    {

    }

    /**
     * ROUTE: /services/toggl/disconnect
     * METHOD: DELETE
     */
    public function disconnect()
    {

    }

    /**
     * ROUTE: /services/toggl/manage
     * METHOD: GET
     */
    public function manage()
    {

    }

    /**
     * ROUTE: /services/toggl/setAttributes
     * METHOD: POST
     */
    public function setAttributes(Request $request)
    {
  
    }

    /**
     * ROUTE: /services/toggl/refreshProjects
     * METHOD: POST
     */
    public function refreshProjects()
    {

    }

    /**
     * ROUTE: /services/toggl/zero
     * METHOD: POST
     */
    public function zero()
    {

    }

}