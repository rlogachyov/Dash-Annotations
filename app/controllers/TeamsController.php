<?php

class TeamsController extends BaseController {

    public function create()
    {
        if(Auth::check())
        {
            $validator = Validator::make(Input::all(), [
                "name" => ["required", "unique:teams,name"],
            ]);

            if($validator->passes()) 
            {
                $name = Input::get('name');
                $team = new Team;
                $team->name = $name;
                Auth::user()->teams()->save($team, array('role' => 'owner'));
                return json_encode(['status' => 'success']);
            }
            else
            {
                return json_encode(['status' => 'error', 'message' => 'Team name already taken']);
            }
            return json_encode(['status' => 'error']);
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }

    public function leave()
    {
        if(Auth::check())
        {
            $name = Input::get('name');
            if(!empty($name))
            {
                $team = Team::where('name', '=', $name)->first();
                if($team)
                {
                    $user = Auth::user();
                    $this->remove_posts($user, $team);
                    $team->users()->detach($user->id);
                    if($team->users()->count() == 0)
                    {
                        $team->delete();
                    }
                    return json_encode(['status' => 'success']);
                }
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }

    public function remove_member()
    {
        if(Auth::check())
        {
            $name = Input::get('name');
            $toRemove = Input::get('username');
            if(!empty($name))
            {
                $team = Team::where('name', '=', $name)->first();
                if($team && $team->owner() && $team->owner()->id == Auth::user()->id)
                {
                    $user = User::where('username', '=', $toRemove)->first();
                    if($user)
                    {
                        $this->remove_posts($user, $team);
                        $team->users()->detach($user->id);
                    }
                    return json_encode(['status' => 'success']);
                }
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }

    public function remove_posts($user, $team)
    {
        foreach($team->entries()->where('user_id', '=', $user->id)->get() as $entry)
        {
            $team->entries()->detach($entry->id);
        }
    }

    public function join()
    {
        if(Auth::check())
        {
            $name = Input::get('name');
            $access_key = Input::get('access_key');
            if(!empty($name))
            {
                $team = Team::where('name', '=', $name)->first();
                if($team)
                {
                    if((empty($team->access_key) && empty($access_key)) || Hash::check($access_key, $team->access_key))
                    {
                        if($team->users()->where('user_id', '=', Auth::user()->id)->first())
                        {
                            return json_encode(['status' => 'error', 'message' => 'You are already a member of this team']);
                        }
                        $team->users()->attach(Auth::user()->id, array('role' => 'member'));
                        return json_encode(['status' => 'success']);
                    }
                    return json_encode(['status' => 'error', 'message' => 'Invalid access key']);
                }
                return json_encode(['status' => 'error', 'message' => 'Team does not exist']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }

    public function set_access_key()
    {
        if(Auth::check())
        {
            $name = Input::get('name');
            $key = Input::get('access_key');
            if(!empty($name))
            {
                $team = Team::where('name', '=', $name)->first();
                if($team && $team->owner() && $team->owner()->id == Auth::user()->id)
                {
                    if(!empty($key))
                    {
                        $key = Hash::make($key);
                    }
                    $team->access_key = $key;
                    $team->save();
                    return json_encode(['status' => 'success']);
                }
                return json_encode(['status' => 'error', 'message' => 'Unknown error']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }

    public function set_role()
    {
        if(Auth::check())
        {
            $name = Input::get('name');
            $username = Input::get('username');
            $role = Input::get('role');
            if(!empty($name) && !empty($role) && !empty($username))
            {
                $team = Team::where('name', '=', $name)->first();
                if($team && $team->owner() && $team->owner()->id == Auth::user()->id)
                {
                    $target_user = $team->users()->where('username', '=', $username)->first();
                    if($target_user)
                    {
                        $team->users()->updateExistingPivot($target_user->id, ['role' => $role]);
                        return json_encode(['status' => 'success']);
                    }
                }
                return json_encode(['status' => 'error', 'message' => 'Unknown error']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Unknown error']);
    }

    public function list_teams()
    {
        if(Auth::check())
        {
            $teams = array();
            foreach(Auth::user()->teams()->get() as $team)
            {
                $teams[] = ['name' => $team->name, 'role' => $team->pivot->role];
            }
            return json_encode(['status' => 'success', 'teams' => $teams]);
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }

    public function list_members()
    {
        if(Auth::check())
        {
            $name = Input::get('name');
            if(!empty($name))
            {
                $members = array();
                $team = Team::where('name', '=', $name)->first();
                if($team && $team->owner() && $team->owner()->id == Auth::user()->id)
                {
                    foreach($team->users()->get() as $user)
                    {
                        $members[] = ['name' => $user->username, 'role' => $user->pivot->role];
                    }
                    return json_encode(['status' => 'success', 'members' => $members, 'has_access_key' => ($team->access_key) ? true : false]);
                }
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Error. Logout and try again']);
    }
}
