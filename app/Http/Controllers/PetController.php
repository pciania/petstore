<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Integrations\Petstore\PetstoreException;
use App\Services\PetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetController extends Controller
{
    public function __construct(
        private readonly PetService $petService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $status = $request->query('status', 'available');

        try {
            $pets = $this->petService->listByStatus((string) $status);
        } catch (PetstoreException $e) {
            return back()->with('error', $this->friendlyError($e));
        }

        return view('pets.index', compact('pets', 'status'));
    }

    public function create(): View
    {
        return view('pets.create');
    }

    public function store(StorePetRequest $request): RedirectResponse
    {
        try {
            $pet = $this->petService->create($request);
        } catch (PetstoreException $e) {
            return back()->withInput()->with('error', $this->friendlyError($e));
        }

        return redirect()
            ->route('pets.show', $pet->id)
            ->with('success', "Pet \"{$pet->name}\" has been created.");
    }

    public function show(int $id): View|RedirectResponse
    {
        try {
            $pet = $this->petService->find($id);
        } catch (PetstoreException $e) {
            if ($e->isNotFound()) {
                return redirect()->route('pets.index')->with('error', "Pet #{$id} not found.");
            }

            return redirect()->route('pets.index')->with('error', $this->friendlyError($e));
        }

        return view('pets.show', compact('pet'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        try {
            $pet = $this->petService->find($id);
        } catch (PetstoreException $e) {
            if ($e->isNotFound()) {
                return redirect()->route('pets.index')->with('error', "Pet #{$id} not found.");
            }

            return redirect()->route('pets.index')->with('error', $this->friendlyError($e));
        }

        return view('pets.edit', compact('pet'));
    }

    public function update(UpdatePetRequest $request, int $id): RedirectResponse
    {
        try {
            $pet = $this->petService->update($request);
        } catch (PetstoreException $e) {
            return back()->withInput()->with('error', $this->friendlyError($e));
        }

        return redirect()
            ->route('pets.show', $pet->id)
            ->with('success', "Pet \"{$pet->name}\" has been updated.");
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->petService->delete($id);
        } catch (PetstoreException $e) {
            return redirect()->route('pets.index')->with('error', $this->friendlyError($e));
        }

        return redirect()
            ->route('pets.index')
            ->with('success', "Pet #{$id} has been deleted.");
    }

    private function friendlyError(PetstoreException $e): string
    {
        if ($e->isNotFound()) {
            return 'The requested pet was not found.';
        }

        if ($e->isClientError()) {
            return 'The request was invalid. Please check your input and try again.';
        }

        return 'Could not reach the Petstore API. Please try again later.';
    }
}

