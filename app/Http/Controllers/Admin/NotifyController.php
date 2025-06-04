<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotifyTemplate;
use Illuminate\Http\Request;

class NotifyController extends Controller
{
    /**
     * List all templates.
     *
     * @return \Illuminate\Http\Response
     */
    public function templates()
    {
        $templates = NotifyTemplate::whereJsonContains('channels', 'inapp')->orWhereJsonContains('channels', 'push')->get();

        return view('admin.templates.index', compact('templates'));
    }

    public function editTemplate($id)
    {
        $template = NotifyTemplate::findorFail($id);

        return view('admin.templates.edit', compact('template'));
    }

    public function updateTemplate($id, Request $request)
    {
        $request->validate([
            'title'   => 'required',
            'message' => 'required',
        ]);
        $template = NotifyTemplate::findOrFail($id);
        $template->push_status = $request->push_status ?? 0;
        $template->message = $request->message;
        $template->title = $request->title;
        $template->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Template updated successfully',
            'url'     => route('admin.notify.templates.index'),
        ], 200);
    }
}
