<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsTrashEntry;
use App\Services\CmsTrash;
use RuntimeException;

class CmsTrashController extends Controller
{
    public function index()
    {
        return view('cms.trash', ['entries' => CmsTrashEntry::with('deletedBy')->latest('id')->paginate(25)]);
    }

    public function restore(CmsTrashEntry $entry, CmsTrash $trash)
    {
        try {
            return back()->with('success', $trash->restore($entry));
        } catch (RuntimeException $e) {
            return back()->withErrors(['trash' => $e->getMessage()]);
        }
    }

    /** Permanent removal is owner-only. Editors can always recover what they removed. */
    public function destroy(CmsTrashEntry $entry, CmsTrash $trash)
    {
        abort_unless(auth('admin')->user()->IsSuper(), 403);
        $trash->purge($entry);

        return back()->with('success', 'Permanently deleted.');
    }
}
