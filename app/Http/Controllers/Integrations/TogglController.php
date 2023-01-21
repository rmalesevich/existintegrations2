<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\TogglProject;
use App\Models\TogglUser;
use App\Models\UserAttribute;
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
        if (auth()->user()->existUser === null) return redirect()->route('home');

        if (auth()->user()->togglUser != null) {
            return redirect()->route('home')
                ->with('errorMessage', __('app.alreadyIntegrated', [ 'service' => 'Toggl Track' ]));
        }

        $this->validate($request, [
            'togglApiToken' => 'required'
        ], [
            'togglApiToken.required' => __('app.addRequestedInformation1Required', [ 'information' => 'Toggl Track API Token' ])
        ]);

        $togglApiToken = $request->togglApiToken;

        $connect = $this->toggl->connect(auth()->user(), $togglApiToken);

        if ($connect->success) {
            return redirect()->route('toggl.manage');
        } else {
            return redirect()->route('add')
                ->with('errorMessage', $connect->message);
        }
    }

    /**
     * ROUTE: /services/toggl/disconnect
     * METHOD: DELETE
     */
    public function disconnect()
    {
        if (auth()->user()->existUser === null || auth()->user()->togglUser === null) return redirect()->route('home');

        $disconnect = $this->toggl->disconnect(auth()->user(), "User Initiated");
        if ($disconnect->success) {
            $successMessage = __('app.serviceDisconnect', ['service' => 'Toggl Track']);
        } else {
            $errorMessage = $disconnect->message ?? __('app.unknownError');
        }

        return redirect()->route('home')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);
    }

    /**
     * ROUTE: /services/toggl/manage
     * METHOD: GET
     */
    public function manage()
    {
        if (auth()->user()->existUser === null || auth()->user()->togglUser === null) return redirect()->route('home');

        $projects = TogglProject::where('user_id', auth()->user()->id)
            ->where('deleted_flag', false)
            ->where('active_flag', true)
            ->get();

        $attributes = array();
        $allAttributes = collect(config('services.toggl.attributes'));
        foreach ($allAttributes as $attribute) {
            if ($attribute['multiple']) {
                // check if this is already configured in a different integration
                $check = UserAttribute::where('user_id', auth()->user()->id)
                    ->where('attribute', $attribute['attribute'])
                    ->whereNot('integration', 'toggl')
                    ->count();
                if ($check == 0) {
                    array_push($attributes, $attribute);
                }
            } else {
                array_push($attributes, $attribute);
            }
        }
        
        return view('manage.toggl', [
            'user' => auth()->user(),
            'userAttributes' => auth()->user()->attributes->where('integration', 'toggl')->where('user_id', auth()->user()->id),
            'attributes' => collect($attributes),
            'projects' => $projects
        ]);
    }

    /**
     * ROUTE: /services/toggl/setAttributes
     * METHOD: POST
     */
    public function setAttributes(Request $request)
    {
        if (auth()->user()->existUser === null || auth()->user()->togglUser === null) return redirect()->route('home');

        $attributes = array();
        foreach (collect(config('services.toggl.attributes')) as $attribute) {
            if (!isset($attributes[$attribute['attribute']])) {
                array_push($attributes, [
                    'attribute' => $attribute['attribute']
                ]);
            }
        }

        $setAttributesResponse = $this->exist->setAttributes(auth()->user(), 'toggl', $attributes, auth()->user()->togglUser->is_new);
        if ($setAttributesResponse->success) {
            $successMessage = __('app.attributeSuccess');

            if (auth()->user()->ynabUser->is_new) {
                TogglUser::where('user_id', auth()->user()->id)
                    ->update([
                        'is_new' => false
                    ]);
            }

            $keys = array_keys($request->project);
            foreach ($keys as $projectId) {
                $attribute = $request->project[$projectId];

                $this->toggl->updateProject(auth()->user(), $projectId, $attribute);
            }

            // zero out any data that has been sent if the categories change
            $days = config('services.baseDays');
            $userAttributes = UserAttribute::where('user_id', auth()->user()->id)
                ->where('integration', 'toggl')
                ->get();

            foreach ($userAttributes as $attribute) {
                $this->exist->zeroUserData(auth()->user(), 'toggl', $attribute->attribute, $days);
            }

        } else {
            $errorMessage = $setAttributesResponse->message ?? __('app.unknownError');
        }

        return redirect()->route('toggl.manage')
            ->with('successMessage', $successMessage ?? null)
            ->with('errorMessage', $errorMessage ?? null);  
    }

    /**
     * ROUTE: /services/toggl/refreshProjects
     * METHOD: POST
     */
    public function refreshProjects()
    {
        if (auth()->user()->existUser === null || auth()->user()->togglUser === null) return redirect()->route('home');

        $this->toggl->processProjects(auth()->user());

        return redirect()->route('toggl.manage')
            ->with('successMessage', __('app.categoryAPISuccess', ['service' => 'Toggl Track', 'category' => 'Projects']));
    }

    /**
     * ROUTE: /services/toggl/zero
     * METHOD: POST
     */
    public function zero()
    {
        if (auth()->user()->existUser === null || auth()->user()->togglUser === null) return redirect()->route('home');

        $days = config('services.baseDays');
        $userAttributes = UserAttribute::where('user_id', auth()->user()->id)
            ->where('integration', 'toggl')
            ->get();

        foreach ($userAttributes as $attribute) {
            $this->exist->zeroUserData(auth()->user(), 'toggl', $attribute->attribute, $days);
        }

        return redirect()->route('toggl.manage')
            ->with('successMessage', __('app.zeroOutSuccess', ['service' => 'Toggl Track', 'days' => $days]));
    }

}