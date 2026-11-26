<?php

namespace Tests\Feature;

use App\Http\Livewire\DocumentJournal;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentJournalLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_journal_filters_documents_with_livewire(): void
    {
        if (!class_exists(Livewire::class)) {
            $this->markTestSkipped('Livewire package is declared in composer.json but is not installed in this local vendor directory.');
        }

        $department = Category::create([
            'name' => 'Departamento de TI',
            'code' => 'IT',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_EMPLOYEE,
        ]);
        $user->categories()->sync([$department->id]);

        $visibleDocument = $this->makeDocument($department, $user, 'Reglamento de acceso a la sala de servidores');
        $hiddenDocument = $this->makeDocument($department, $user, 'Solicitud de compra de portátiles');

        Livewire::actingAs($user)
            ->test(DocumentJournal::class)
            ->assertSee($visibleDocument->name)
            ->assertSee($hiddenDocument->name)
            ->set('search', 'servidores')
            ->assertSee($visibleDocument->name)
            ->assertDontSee($hiddenDocument->name);
    }

    protected function makeDocument(Category $department, User $author, string $name): Document
    {
        return Document::create([
            'name' => $name,
            'registration_number' => 'INT-' . now()->format('Y') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'document_type' => 'internal',
            'status' => 'draft',
            'priority' => 'normal',
            'file' => 'test.txt',
            'summary' => 'Prueba del registro Livewire.',
            'external_partner' => null,
            'category_id' => $department->id,
            'author_id' => $author->id,
            'visibility' => true,
            'workflow_round' => 0,
        ]);
    }
}
