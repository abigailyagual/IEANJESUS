<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        File::ensureDirectoryExists(public_path('files'));

        $departments = collect([
            ['name' => 'Finanzas', 'code' => 'FIN'],
            ['name' => 'Recursos humanos', 'code' => 'HR'],
            ['name' => 'Departamento jurídico', 'code' => 'LAW'],
            ['name' => 'Compras', 'code' => 'PROC'],
            ['name' => 'Departamento de TI', 'code' => 'IT'],
            ['name' => 'Ventas', 'code' => 'SALE'],
        ])->mapWithKeys(function (array $department) {
            $model = Category::create($department);

            return [$department['code'] => $model];
        });

        $admin = $this->makeUser([
            'name' => 'Alekséi Oréjov',
            'email' => 'admin@edo.local',
            'position' => 'Administrador del sistema',
            'phone' => '+7 900 000-00-01',
            'password' => 'Admin12345',
            'role' => User::ROLE_ADMIN,
        ], $departments->pluck('id')->all());

        $office = $this->makeUser([
            'name' => 'Marina Bélova',
            'email' => 'office@edo.local',
            'position' => 'Asistente de secretaría',
            'phone' => '+7 900 000-00-02',
            'password' => 'Office12345',
            'role' => User::ROLE_OFFICE,
        ], $departments->pluck('id')->all());

        $financeHead = $this->makeUser([
            'name' => 'Irina Nóvikova',
            'email' => 'fin.head@edo.local',
            'position' => 'Director de finanzas',
            'phone' => '+7 900 000-00-03',
            'password' => 'Workflow123',
            'role' => User::ROLE_DEPARTMENT_HEAD,
        ], [$departments['FIN']->id]);

        $hrHead = $this->makeUser([
            'name' => 'Tatiana Zajárova',
            'email' => 'hr.head@edo.local',
            'position' => 'Director de recursos humanos',
            'phone' => '+7 900 000-00-04',
            'password' => 'Workflow123',
            'role' => User::ROLE_DEPARTMENT_HEAD,
        ], [$departments['HR']->id]);

        $itHead = $this->makeUser([
            'name' => 'Serguéi Vólkov',
            'email' => 'it.head@edo.local',
            'position' => 'Director de TI',
            'phone' => '+7 900 000-00-05',
            'password' => 'Workflow123',
            'role' => User::ROLE_DEPARTMENT_HEAD,
        ], [$departments['IT']->id]);

        $procurementHead = $this->makeUser([
            'name' => 'Olga Románova',
            'email' => 'proc.head@edo.local',
            'position' => 'Director de compras',
            'phone' => '+7 900 000-00-06',
            'password' => 'Workflow123',
            'role' => User::ROLE_DEPARTMENT_HEAD,
        ], [$departments['PROC']->id]);

        $lawyer = $this->makeUser([
            'name' => 'Anna Vlásova',
            'email' => 'lawyer@edo.local',
            'position' => 'Asesor jurídico',
            'phone' => '+7 900 000-00-07',
            'password' => 'Employee123',
            'role' => User::ROLE_EMPLOYEE,
        ], [$departments['LAW']->id]);

        $accountant = $this->makeUser([
            'name' => 'Ígor Smirnov',
            'email' => 'accountant@edo.local',
            'position' => 'Contador principal',
            'phone' => '+7 900 000-00-08',
            'password' => 'Employee123',
            'role' => User::ROLE_EMPLOYEE,
        ], [$departments['FIN']->id]);

        $hrSpecialist = $this->makeUser([
            'name' => 'Elena Kovaliova',
            'email' => 'hr@edo.local',
            'position' => 'Responsable de recursos humanos',
            'phone' => '+7 900 000-00-09',
            'password' => 'Employee123',
            'role' => User::ROLE_EMPLOYEE,
        ], [$departments['HR']->id]);

        $procurementManager = $this->makeUser([
            'name' => 'Maksim Orlov',
            'email' => 'procurement@edo.local',
            'position' => 'Responsable de compras',
            'phone' => '+7 900 000-00-10',
            'password' => 'Employee123',
            'role' => User::ROLE_EMPLOYEE,
        ], [$departments['PROC']->id]);

        $systemAnalyst = $this->makeUser([
            'name' => 'Denis Kuznetsov',
            'email' => 'analyst@edo.local',
            'position' => 'Analista de sistemas',
            'phone' => '+7 900 000-00-11',
            'password' => 'Employee123',
            'role' => User::ROLE_EMPLOYEE,
        ], [$departments['IT']->id]);

        /** @var DocumentWorkflowService $workflow */
        $workflow = app(DocumentWorkflowService::class);

        $approvedOrder = $this->createDocument([
            'name' => 'Orden de inventario anual',
            'registration_number' => 'ORD-' . now()->format('Y') . '-0001',
            'document_type' => 'order',
            'priority' => 'high',
            'summary' => 'Inventario de activos fijos y existencias de todos los departamentos.',
            'category_id' => $departments['FIN']->id,
            'visibility' => true,
            'due_at' => now()->addDays(5),
            'file' => $this->makeDemoFile('inventory-order.txt', 'Orden de inventario anual'),
            'external_partner' => null,
        ], $accountant, $admin);

        $workflow->log($approvedOrder, $accountant, 'created', 'Documento creado', 'Finanzas preparó el borrador de la orden.');
        $workflow->submit($approvedOrder, $accountant);
        $workflow->approve($approvedOrder, $financeHead, 'Confirmo el inventario del departamento.');
        $workflow->approve($approvedOrder, $admin, 'Flujo aprobado por la administración.');
        $workflow->approve($approvedOrder, $office, 'Documento registrado en secretaría.');

        $contract = $this->createDocument([
            'name' => 'Contrato de mantenimiento de infraestructura de servidores',
            'registration_number' => 'CTR-' . now()->format('Y') . '-0002',
            'document_type' => 'contract',
            'priority' => 'critical',
            'summary' => 'Renovación del contrato de alojamiento, copias de seguridad y monitoreo.',
            'category_id' => $departments['IT']->id,
            'visibility' => true,
            'due_at' => now()->addDays(2),
            'file' => $this->makeDemoFile('infra-contract.txt', 'Contrato de mantenimiento de infraestructura de servidores'),
            'external_partner' => 'TecnoService Ltda.',
        ], $systemAnalyst, $lawyer);

        $workflow->log($contract, $systemAnalyst, 'created', 'Documento creado', 'TI preparó el borrador del contrato.');
        $workflow->submit($contract, $systemAnalyst);
        $workflow->approve($contract, $itHead, 'Condiciones técnicas verificadas, puede enviarse al asesor jurídico.');

        $draftMemo = $this->createDocument([
            'name' => 'Memorando de contratación de analista de ventas',
            'registration_number' => 'INT-' . now()->format('Y') . '-0003',
            'document_type' => 'internal',
            'priority' => 'normal',
            'summary' => 'Justificación de la vacante y cálculo del presupuesto salarial.',
            'category_id' => $departments['HR']->id,
            'visibility' => true,
            'due_at' => now()->addDays(4),
            'file' => $this->makeDemoFile('sales-analyst-hire.txt', 'Memorando de contratación de analista de ventas'),
            'external_partner' => null,
        ], $hrSpecialist, $admin);

        $workflow->log($draftMemo, $hrSpecialist, 'created', 'Documento creado', 'Borrador en preparación para su envío.');

        $rejectedLetter = $this->createDocument([
            'name' => 'Carta al proveedor para revisar el cronograma de entregas',
            'registration_number' => 'OUT-' . now()->format('Y') . '-0004',
            'document_type' => 'outgoing',
            'priority' => 'high',
            'summary' => 'Propuesta de cambio del cronograma por el aumento estacional de ventas.',
            'category_id' => $departments['PROC']->id,
            'visibility' => true,
            'due_at' => now()->subDay(),
            'file' => $this->makeDemoFile('supplier-letter.txt', 'Carta saliente al proveedor'),
            'external_partner' => 'SeverSnab S.A.',
        ], $procurementManager, $lawyer);

        $workflow->log($rejectedLetter, $procurementManager, 'created', 'Documento creado', 'Compras preparó la carta al proveedor.');
        $workflow->submit($rejectedLetter, $procurementManager);
        $workflow->approve($rejectedLetter, $procurementHead, 'Puede enviarse al departamento jurídico.');
        $workflow->reject($rejectedLetter, $lawyer, 'Añada la referencia al anexo con el nuevo cronograma y precise los plazos de vigencia.');

        $archivedAct = $this->createDocument([
            'name' => 'Acta entrante de conciliación de cuentas',
            'registration_number' => 'IN-' . now()->format('Y') . '-0005',
            'document_type' => 'incoming',
            'priority' => 'normal',
            'summary' => 'Acta de conciliación del primer trimestre del proveedor principal.',
            'category_id' => $departments['FIN']->id,
            'visibility' => true,
            'due_at' => now()->subDays(15),
            'file' => $this->makeDemoFile('reconciliation-act.txt', 'Acta entrante de conciliación de cuentas'),
            'external_partner' => 'Alliance Logistic Ltda.',
        ], $accountant, $admin);

        $workflow->log($archivedAct, $accountant, 'created', 'Documento creado', 'Acta entrante registrada en finanzas.');
        $workflow->submit($archivedAct, $accountant);
        $workflow->approve($archivedAct, $financeHead, 'Indicadores financieros confirmados.');
        $workflow->approve($archivedAct, $admin, 'Documento aprobado por la administración.');
        $workflow->approve($archivedAct, $office, 'Documento registrado y cerrado en secretaría.');
        $workflow->archive($archivedAct, $office, 'Registro enviado al archivo electrónico al finalizar el flujo.');
    }

    protected function makeUser(array $attributes, array $categoryIds): User
    {
        $user = User::create([
            ...$attributes,
            'email_verified_at' => now(),
        ]);

        $user->categories()->sync($categoryIds);

        return $user;
    }

    protected function createDocument(array $attributes, User $author, ?User $approver = null): Document
    {
        return Document::create([
            ...$attributes,
            'author_id' => $author->id,
            'approver_id' => $approver?->id,
            'status' => 'draft',
            'submitted_at' => null,
            'approved_at' => null,
            'archived_at' => null,
            'rejection_reason' => null,
            'workflow_round' => 0,
        ]);
    }

    /**
     * Create a demo text file in the public files folder.
     */
    protected function makeDemoFile(string $fileName, string $content): string
    {
        File::put(public_path('files/' . $fileName), $content);

        return $fileName;
    }
}
