<?php

namespace App\Http\Livewire;

use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Rules\{Rule, RuleActions};
use PowerComponents\LivewirePowerGrid\Traits\ActionButton;
use PowerComponents\LivewirePowerGrid\{Button, Column, Exportable, Footer, Header, PowerGrid, PowerGridComponent, PowerGridEloquent};

final class TicketTable extends PowerGridComponent
{
    use ActionButton;

    /*
    |--------------------------------------------------------------------------
    |  Features Setup
    |--------------------------------------------------------------------------
    | Setup Table's general features
    |
    */
    public function setUp(): array
    {
        return [
            Header::make()->showSearchInput(),
            Footer::make()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    |  Datasource
    |--------------------------------------------------------------------------
    | Provides data to your Table using a Model or Collection
    |
    */

    /**
    * PowerGrid datasource.
    *
    * @return Builder<\App\Models\Ticket>
    */
    public function datasource(): Builder
    {
        return Ticket::query()
                    -> leftJoin('users as user', function($users) {
                        $users->on('tickets.user_id', '=', 'user.id');
                    })
                    -> leftJoin('users as cashier_user', function($users) {
                        $users->on('tickets.third_party_cashier_employee_id', '=', 'cashier_user.id');
                    })
                    -> leftJoin('ticket_or_entry_types as type', function($types) {
                        $types->on('tickets.type', '=', 'type.id');
                    })
                    -> select([
                        'tickets.id',
                        'type.description as type',
                        'user.name as users_name',
                        'tickets.amount',
                        'cashier_user.name as cashier_name',
                        'tickets.date_time'
                    ])
                    ->orderBy('date_time', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    |  Relationship Search
    |--------------------------------------------------------------------------
    | Configure here relationships to be used by the Search and Table Filters.
    |
    */

    /**
     * Relationship search.
     *
     * @return array<string, array<int, string>>
     */
    public function relationSearch(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    |  Add Column
    |--------------------------------------------------------------------------
    | Make Datasource fields available to be used as columns.
    | You can pass a closure to transform/modify the data.
    |
    */
    public function addColumns(): PowerGridEloquent
    {
        return PowerGrid::eloquent()
            ->addColumn('type')
            ->addColumn('users_name')
            ->addColumn('amount')
            ->addColumn('cashier_name')
            ->addColumn('date_time', function (Ticket $ticket) {
                return Carbon::parse($ticket->date_time)->format('d/m/Y H:i');
            });
    }

    /*
    |--------------------------------------------------------------------------
    |  Include Columns
    |--------------------------------------------------------------------------
    | Include the columns added columns, making them visible on the Table.
    | Each column can be configured with properties, filters, actions...
    |
    */

     /**
     * PowerGrid Columns.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return [
            Column::make('TIPO', 'type')
                ->searchable()
                ->sortable(),

            Column::make('USUÁRIO', 'users_name')
                ->searchable()
                ->sortable(),

            Column::make('QUANTIDADE', 'amount')
                ->sortable(),

            Column::make('VENDEDOR', 'cashier_name')
                ->searchable()
                ->sortable(),

            Column::make('DATA', 'date_time')
                ->searchable()
                ->sortable(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Actions Method
    |--------------------------------------------------------------------------
    | Enable the method below only if the Routes below are defined in your app.
    |
    */

     /**
     * PowerGrid Ticket Action Buttons.
     *
     * @return array<int, Button>
     */

    /*
    public function actions(): array
    {
       return [
           Button::make('edit', 'Edit')
               ->class('bg-indigo-500 cursor-pointer text-white px-3 py-2.5 m-1 rounded text-sm')
               ->route('ticket.edit', ['ticket' => 'id']),

           Button::make('destroy', 'Delete')
               ->class('bg-red-500 cursor-pointer text-white px-3 py-2 m-1 rounded text-sm')
               ->route('ticket.destroy', ['ticket' => 'id'])
               ->method('delete')
        ];
    }
    */

    /*
    |--------------------------------------------------------------------------
    | Actions Rules
    |--------------------------------------------------------------------------
    | Enable the method below to configure Rules for your Table and Action Buttons.
    |
    */

     /**
     * PowerGrid Ticket Action Rules.
     *
     * @return array<int, RuleActions>
     */

    /*
    public function actionRules(): array
    {
       return [

           //Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($ticket) => $ticket->id === 1)
                ->hide(),
        ];
    }
    */
}
