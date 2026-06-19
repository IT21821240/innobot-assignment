<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNoteRequest;
use App\Models\Notes;

class NotesController extends Controller
{
    public function index(Request $request)
    {
        $query = Notes::query();

        if ($request->priority) {
            $query->where(
                'priority',
                $request->priority
            );
        }

        return response()->json(
            $query->latest()->get()
        );
    }

    public function store(
        StoreNoteRequest $request
    ) {
        $note = Notes::create(
            $request->validated()
        );

        return response()->json(
            $note,
            201
        );
    }

    public function archive(Notes $note)
    {
        $note->update([
            'archived' => true
        ]);

        return response()->json([
            'message' => 'Note archived'
        ]);
    }
}
