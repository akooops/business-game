<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\FileService;
use App\Services\IndexService;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = IndexService::limitPerPage($request->query('perPage', 10));
        $page = IndexService::checkPageIfNull($request->query('page', 1));
        $search = IndexService::checkIfSearchEmpty($request->query('search'));

        $users = User::latest();

        if ($search) {
            $users->where(function($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search);
                }
                $query->orWhere('firstname', 'like', '%' . $search . '%')
                      ->orWhere('lastname', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%')
                      ->orWhereHas('company', function($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                      });
            });
        }

        $users = $users->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'users' => $users->items(),
                'pagination' => IndexService::handlePagination($users)
            ]);
        }

        return inertia('Admin/Users/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return inertia('Admin/Users/Create');
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
    
        if($request->has('file')){
            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the user
            FileService::linkModel($file, 'user', $user->id, 1);
        }

        return inertia('Admin/Users/Index', [
            'success' => 'User created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {    
        return inertia('Admin/Users/Show', compact('user'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return inertia('Admin/Users/Edit', compact('user'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, UpdateUserRequest $request)
    {
        if($request->get('password') != null){
            $user->update([
                'password' => $request->get('password'),
            ]);
        }

        $user->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        if($request->file('file')){
            //Delete the old file if it exists
            if($user->file){
                FileService::delete($user->file);
            }

            //Upload the new file
            $file = FileService::upload($request->file('file'));

            //Link the file to the user
            FileService::linkModel($file, 'user', $user->id, 1);
        }
    
        return inertia('Admin/Users/Index', [
            'success' => 'User updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success','User deleted successfully');
    }
}
