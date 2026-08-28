<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Управляет комментариями к клиентам и записям.
 */
class CommentController extends Controller
{
    /** Создаёт комментарий к выбранной сущности. */
    public function store(Request $request, string $type, int $id)
    {
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $model = $this->model($type, $id);
        $comment = $model->comments()->create($data + ['user_id' => $request->user()->id]);

        return response()->json($comment->load('user:id,name'), 201);
    }

    /** Удаляет комментарий, если пользователь имеет соответствующие права. */
    public function destroy(Request $request, Comment $comment)
    {
        abort_unless(
            $request->user()->isAdmin() || $comment->user_id === $request->user()->id,
            403,
        );
        $comment->delete();

        return response()->noContent();
    }

    /** Возвращает комментируемую сущность по её типу и идентификатору. */
    private function model(string $type, int $id): Model
    {
        return match ($type) {
            'clients' => Client::findOrFail($id),
            'appointments' => Appointment::findOrFail($id),
            default => abort(404),
        };
    }
}
