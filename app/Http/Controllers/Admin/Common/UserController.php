<?php

namespace App\Http\Controllers\Admin\Common;

use App\Http\Controllers\Controller;
use App\Models\Common\Bookmark;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Common\Favorite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Models\Common\UserNotepad;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->where('id', '!=', Auth::user()->id)->paginate(2000);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:' . implode(',', getAllRoles()),
        ]);

        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'you do not have permission to create users.');
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->email_verified_at = now();
        $user->save();

        logActivity(Auth::user(), 'Create', 'Created a new user : ' . $user->name . ' ( ID: ' . $user->id . ')');

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    // UserController
    public function Details(User $user)
    {
        $FavoritePosts = Favorite::where('user_id', $user->id)->get();
        // Attach post data dynamically
        $FavoritePosts->transform(function ($fav) {
            $modelClass = getModelByLanguageAndType($fav->language, $fav->post_type);
            if ($modelClass && class_exists($modelClass)) {
                $fav->post = $modelClass::find($fav->post_id);
            } else {
                $fav->post = null;
            }
            return $fav;
        });
        // Group by language (only those with post data)
        $GroupedFavorites = $FavoritePosts
            ->filter(fn($fav) => $fav->post) // skip missing posts
            ->groupBy('language')
            ->sortBy(function ($items, $language) {
                return $language === 'english' ? 0 : 1;
            });

        ///
        $BookmarkPosts = Bookmark::where('user_id', $user->id)->get();
        // Attach post data dynamically
        $BookmarkPosts->transform(function ($bookmark) {
            $modelClass = getModelByLanguageAndType($bookmark->language, $bookmark->post_type);
            if ($modelClass && class_exists($modelClass)) {
                $bookmark->post = $modelClass::find($bookmark->post_id);
            } else {
                $bookmark->post = null;
            }
            return $bookmark;
        });
        // Group by language (only those with post data)
        $GroupedBookmarkPosts = $BookmarkPosts
            ->filter(fn($bookmark) => $bookmark->post) // skip missing posts
            ->groupBy('language')
            ->sortBy(function ($items, $language) {
                return $language === 'english' ? 0 : 1;
            });

        $notes = UserNotepad::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $GroupedNotes = $notes->groupBy('language')->sortBy(function ($notes, $language) {
            return $language === 'english' ? 0 : 1;
        });

        return view('admin.users.details', compact('user', 'GroupedFavorites', 'GroupedBookmarkPosts', 'GroupedNotes'));
    }



    // Show the edit form
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Update user data
    public function update(Request $request, User $user)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:' . implode(',', getAllRoles()),
        ]);

        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'you do not have permission to update users.');
        }
        if (Auth::user()->id === $user->id && $request->email !== $user->email) {
            return redirect()->back()->with('error', 'you cannot change your own email.');
        }
        if (Auth::user()->id === $user->id && $request->role !== $user->role) {
            return redirect()->back()->with('error', 'you cannot change your own role.');
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update email verified at
        $user->email_verified_at = $request->has('email_verified') ? now() : null;

        $user->save();

        logActivity(Auth::user(), 'Update', 'Updated one user : ' . $user->name);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function editRole(User $user)
    {
        $roles = ['editor' => 'Editor', 'subscriber' => 'Subscriber'];
        return view('admin.users.edit-role', compact('user', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:' . implode(',', getAllRoles()),
        ]);

        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'User role updated successfully!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        logActivity(Auth::user(), 'Delete', 'Deleted one user : ' . $user->name . ' (ID: ' . $user->id . ')');
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
