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
        // Fix: Usa eager loading corretto per la relazione belongsTo
        $users = User::with('roles')->select('*');

        return DataTables::of($users)
            ->addColumn('roles', function (User $user) {
                // Fix: gestisce la relazione belongsTo corretta
                if ($user->roles && $user->roles->name) {
                    return $user->roles->name;
                }

                // Fallback basato sul campo role numerico
                switch ($user->role) {
                    case '1':
                    case 1:
                        return 'Amministratore';
                    case '2':
                    case 2:
                        return 'Utente';
                    default:
                        return 'Non definito';
                }
            })
            ->addColumn('abilitato', function (User $user) {
                // Fix: controlla il campo abilitato correttamente
                // Gestisce sia string che boolean che int
                $isEnabled = $user->abilitato == 1 ||
                    $user->abilitato === true ||
                    $user->abilitato === '1' ||
                    strtolower($user->abilitato) === 'si';

                if ($isEnabled) {
                    return '<span class="status-badge active">
                    <i class="fas fa-check-circle"></i>
                    Attivo
                </span>';
                } else {
                    return '<span class="status-badge disabled">
                    <i class="fas fa-times-circle"></i>
                    Disabilitato
                </span>';
                }
            })
            ->addColumn('email_verified', function (User $user) {
                // Controlla se l'email è verificata
                $isVerified = !is_null($user->email_verified_at);

                if ($isVerified) {
                    return '<span class="status-badge active">
                        <i class="fas fa-check-circle"></i>
                        Verificata
                    </span>';
                } else {
                    return '<span class="status-badge disabled">
                        <i class="fas fa-times-circle"></i>
                        Non Verificata
                    </span>';
                }
            })
            ->addColumn('actions', function ($user) {
                $actions = '<div class="action-buttons">';

                // Pulsante Modifica
                $actions .= '<a href="' . route('users.edit', $user->id) . '" 
                class="btn btn-sm btn-info" 
                data-bs-toggle="tooltip" 
                title="Modifica utente">
                <i class="fas fa-edit"></i>
            </a>';

                // Pulsanti Lock/Unlock solo se non è l'utente corrente
                if ($user->id !== Auth::id()) {
                    $userName = trim($user->name . ' ' . ($user->surname ?? ''));
                    $isEnabled = $user->abilitato == 1 ||
                        $user->abilitato === true ||
                        $user->abilitato === '1' ||
                        strtolower($user->abilitato) === 'si';

                    if ($isEnabled) {
                        // Pulsante Lock (Disabilita)
                        $actions .= '<button 
                        data-id="' . $user->id . '" 
                        data-name="' . htmlspecialchars($userName) . '"
                        class="btn btn-sm btn-danger lockButton" 
                        data-bs-toggle="tooltip" 
                        title="Disabilita utente">
                        <i class="fas fa-user-times"></i>
                    </button>';
                    } else {
                        // Pulsante Unlock (Abilita)
                        $actions .= '<button 
                        data-id="' . $user->id . '" 
                        data-name="' . htmlspecialchars($userName) . '"
                        class="btn btn-sm btn-success unlockButton" 
                        data-bs-toggle="tooltip" 
                        title="Abilita utente">
                        <i class="fas fa-user-check"></i>
                    </button>';
                    }

                    // Pulsanti Verifica Email
                    $isVerified = !is_null($user->email_verified_at);

                    if (!$isVerified) {
                        // Pulsante Reinvia Email Verifica
                        $actions .= '<button 
                        data-id="' . $user->id . '" 
                        data-name="' . htmlspecialchars($userName) . '"
                        class="btn btn-sm btn-warning resendVerificationButton" 
                        data-bs-toggle="tooltip" 
                        title="Reinvia email verifica">
                        <i class="fas fa-envelope"></i>
                    </button>';

                        // Pulsante Forza Verifica
                        $actions .= '<button 
                        data-id="' . $user->id . '" 
                        data-name="' . htmlspecialchars($userName) . '"
                        class="btn btn-sm btn-primary forceVerifyButton" 
                        data-bs-toggle="tooltip" 
                        title="Forza verifica email">
                        <i class="fas fa-check-double"></i>
                    </button>';
                    }
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions', 'abilitato', 'email_verified'])
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

    /**
     * Reinvia email di verifica
     */
    public function resendVerification(Request $request)
    {
        $user = User::find($request->userId);

        if (!$user) {
            return response()->json(['error' => 'Utente non trovato.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => 'Email già verificata.'], 400);
        }

        try {
            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => 'Email di verifica inviata con successo.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore durante l\'invio dell\'email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forza verifica email (senza invio email)
     */
    public function forceVerify(Request $request)
    {
        $user = User::find($request->userId);

        if (!$user) {
            return response()->json(['error' => 'Utente non trovato.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => 'Email già verificata.'], 400);
        }

        try {
            $user->markEmailAsVerified();

            // Abilita automaticamente l'utente
            $user->abilitato = true;
            $user->save();

            return response()->json([
                'success' => 'Email verificata con successo e utente abilitato.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Errore durante la verifica: ' . $e->getMessage()
            ], 500);
        }
    }
}
