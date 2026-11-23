<?php

namespace App\Http\Controllers;

use App\Models\NdtcDisposingEntity;
use Illuminate\Http\Request;

class NdtcDisposingEntityController extends Controller
{
    // autocomplete for the order form (route is limited to admin / ndtc_manager)
    // ?field=name|address_line_1|city|zip|phone_number&q=...  (field defaults to name)
    public function search(Request $request)
    {
        $field = in_array($request->query('field'), ['name', 'address_line_1', 'city', 'zip', 'phone_number'], true)
            ? $request->query('field')
            : 'name';

        $q = str_replace(['%', '_'], ['\\%', '\\_'], trim((string) $request->query('q')));

        return NdtcDisposingEntity::where($field, 'like', "%{$q}%")
            ->orderByRaw("{$field} like ? desc", ["{$q}%"])
            ->orderBy($field)
            ->orderBy('name')
            ->limit(10)
            ->get(['name', 'address_line_1', 'address_line_2', 'city', 'state',
                   'zip', 'county', 'phone_number', 'phone_type']);
    }

    // admin screens
    public function index()
    {
        $this->authorizeAdmin();

        return view('pages.ndtc.entities.index', [
            'entities' => NdtcDisposingEntity::orderBy('name')->get(), // DataTables pages and searches in the browser
        ]);
    }

    public function edit(NdtcDisposingEntity $entity)
    {
        $this->authorizeAdmin();

        return view('pages.ndtc.entities.edit', compact('entity'));
    }

    public function update(Request $request, NdtcDisposingEntity $entity)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'name'           => 'required|string|max:255|unique:ndtc_disposing_entities,name,'.$entity->id,
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:255',
            'state'          => 'nullable|string|size:2',
            'zip'            => 'nullable|string|max:10',
            'county'         => 'nullable|string|max:255',
            'phone_number'   => 'nullable|string|max:30',
            'phone_type'     => 'nullable|string|max:20',
        ]);

        $entity->update($data);

        return redirect()->route('ndtc.entities.index')->with('success', 'Disposing entity updated');
    }

    public function destroy(NdtcDisposingEntity $entity)
    {
        // only a full admin may delete a saved entity
        abort_unless(auth()->user()->role === 'admin', 403);

        $entity->delete();

        return redirect()->route('ndtc.entities.index')->with('success', 'Disposing entity deleted');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'ndtc_manager']), 403);
    }
}
