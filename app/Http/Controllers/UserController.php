<?php

// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        // Controlla se l'utente è un admin
        if (Auth::user()->role !== '1') {
            return redirect('/home')->with('error', 'Access denied');
        }

        return view('admin.users.index');
    }


    public function edit(Request $request)
    {
        $user = User::findOrFail($request->id); // Trova l'utente da modificare

        return view('admin.users.edit', compact('user')); // Mostra la vista per modificare l'utente
    }


    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);

        $rules = [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->id,
            'role' => 'required|in:1,2',
            'abilitato' => 'required|boolean',
        ];

        $messages = [
            'name.required' => 'Inserire il nome',
            'surname.required' => 'Inserire il cognome',
            'email.required' => 'Inserire la mail',
            'email.email' => 'La mail deve avere un formato valido',
            'email.unique' => 'Mail già utilizzata per la registrazione',
        ];



        // Validazione del form
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'response' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Aggiorna i dati dell'utente        
        $user->update([
            'name' => $request->input('name'),
            'surname' => $request->input('surname'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'abilitato' => $request->input('abilitato'),
        ]);

        // Risposta di successo
        /* return response()->json(['success' => 'Utente aggiornato con successo.']); */
        return response()->json([
            'response' => true,
        ]);
    }

    public function getUsersData()
    {
        $users = User::with('roles')->select('users.*');

        return DataTables::of($users)
            ->addColumn('roles', function (User $user) {
                return $user->roles->name;
            })
            ->addColumn('abilitato', function (User $user) {
                return $user->abilitato
                    ? '<span class="badge bg-success">SI</span>'
                    : '<span class="badge bg-danger">NO</span>';
            })
            ->addColumn('actions', function ($user) {
                $commonClass = 'btn btn-sm d-inline-flex align-items-center justify-content-center';
                $editButton = '<a href="' . route('users.edit', $user->id) . '" class="' . $commonClass . ' btn-outline-primary" style="min-width: 36px;" title="Modifica utente"><i class="fa-solid fa-user-pen"></i></a>';

                $lockButton = $unlockButton = '';

                if ($user->id !== Auth::id()) {
                    if ($user->abilitato) {
                        $lockButton = '<button data-id="' . $user->id . '" class="' . $commonClass . 'btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center lockButton" data-bs-toggle="tooltip" title="Disabilita utente"><i class="fa-solid fa-lock"></i></button>';
                    } else {
                        $unlockButton = '<button data-id="' . $user->id . '" class="' . $commonClass . 'btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center unlockButton" data-bs-toggle="tooltip" title="Abilita utente"><i class="fa-solid fa-unlock"></i></button>';
                    }
                }

                return '<div class="d-flex justify-content-center align-items-center gap-2 flex-nowrap">'
                    . $editButton
                    . $lockButton
                    . $unlockButton
                    . '</div>';
            })
            ->rawColumns(['actions', 'abilitato'])
            ->make(true);
    }

    public function unlock(Request $request)
    {
        $user = User::find($request->userId);
        if ($user) {
            $user->abilitato = true;
            $user->save();

            return response()->json(['success' => 'Utente abilitato con successo.']);
        }
        return response()->json(['error' => 'Utente non trovato.'], 404);
    }

    public function lock(Request $request)
    {
        $user = User::find($request->userId);
        if ($user) {
            $user->abilitato = false;
            $user->save();

            return response()->json(['success' => 'Utente disabilitato con successo.']);
        }
        return response()->json(['error' => 'Utente non trovato.'], 404);
    }
}
