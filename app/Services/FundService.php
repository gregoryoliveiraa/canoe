<?php

namespace App\Services;

use App\Events\DuplicateFundWarningEvent;
use App\Http\Controllers\Requests\FundPostRequest;
use App\Models\Fund;
use App\Models\FundManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FundService
{
    public function readFund(Request $request)
    {
        try {
            Log::debug('Read Fund | A request has been initiated:', $request->toArray());

            $validatedData = $this->validateReadFundRequest($request);

            $funds = $this->filterFunds($validatedData);

            Log::debug('Read Fund | A request has been finished.', []);

            return response()->json([
                'success' => true,
                'data' => $funds,
                'potentialDuplicates' => $this->getPotentialDuplicates(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createFund(Request $request)
    {
        try {
            $fundName = $request->fund;
            $managerName = $request->manager;

            Log::debug('Create Fund | Request Data:', $request->all());

            $duplicateResult = $this->isDuplicateFund($fundName, $managerName);

            if ($duplicateResult['isDuplicateFund']) {
                Log::debug('Create Fund | Duplicate event has been triggered.', []);
                event(new DuplicateFundWarningEvent($fundName, $managerName, $duplicateResult));
            }

            $manager = $this->createManager($request->manager);

            $fund = new Fund();
            $fund->name = $request->fund;
            $fund->manager_id = $manager->id;
            $fund->start_year = $request->year;
            $fund->aliases = $request->alias;
            $fund->save();

            Log::debug('Create Fund | A new Fund has been created.', $fund->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Fund has been created successfully.',
                'data' => $fund
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the fund.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateFund(FundPostRequest $request, $id)
    {
        try {
            Log::debug('Update Fund | Request Data:', $request->all());

            $fund = Fund::findOrFail($id);
            $this->updateManagerAndFund($request, $fund);

            return response()->json([
                'success' => true,
                'message' => 'Fund has been updated successfully.',
                'data' => $fund,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the fund.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function createManager(string $managerName): FundManager
    {
        $manager = new FundManager();
        $manager->name = $managerName;
        $manager->save();

        Log::debug('Create Fund | A Manager has been created:', [$manager]);

        return $manager;
    }

    private function checkForDuplicateFund(string $fundName, string $managerName): array
    {
        $result = [
            'isDuplicateFund' => false,
            'matchAlias' => null,
            'matchFundName' => null,
        ];

        $existingFund = $this->findFundWithAliasOrName($fundName, $managerName);

        if ($existingFund->count() > 0) {
            $result = $this->identifyMatchingCriteria($result, $existingFund, $fundName);
        }

        return $result;
    }

    private function findFundWithAliasOrName(string $fundName, string $managerName)
    {
        return Fund::where(function ($query) use ($fundName) {
            $query->where('name', $fundName)
                ->orWhereJsonContains('aliases', $fundName);
        })
            ->whereHas('manager', function ($query) use ($managerName) {
                $query->where('name', $managerName);
            })
            ->get();
    }

    private function identifyMatchingCriteria(array $result, $existingFund, string $fundName): array
    {
        $result['isDuplicateFund'] = true;

        $nameMatch = $existingFund->contains('name', $fundName);
        $aliasMatch = $existingFund->contains(function ($fund) use ($fundName) {
            return in_array($fundName, $fund->aliases);
        });

        if ($nameMatch && $aliasMatch) {
            $result['matchAlias'] = $fundName;
            $result['matchFundName'] = $fundName;
        } elseif ($nameMatch) {
            $result['matchFundName'] = $fundName;
        } elseif ($aliasMatch) {
            $matchingFund = $existingFund->where('aliases', 'LIKE', '%"' . $fundName . '"%')->first();
            $result['matchAlias'] = $matchingFund->aliases;
        }

        return $result;
    }

    private function fetchPotentialDuplicates(): array
    {
        $potentialDuplicates = [];

        $subquery1 = $this->generateFundAndManagerJoinSubQuery();
        $subquery2 = $this->generateCrossJoinWithAliasesSubQuery();

        $potentialDuplicates = DB::query()->fromSub($subquery1->union($subquery2), 'merged_subquery')->get();

        foreach ($potentialDuplicates as $index => $row) {
            $potentialDuplicates[$index]->fund_aliases = json_decode($row->fund_aliases, true);
        }

        Log::debug('Read Fund | A potential duplicate has been searched:', [$potentialDuplicates]);

        return $potentialDuplicates->toArray();
    }

    private function generateFundAndManagerJoinSubQuery()
    {
        return DB::table('funds as f1')
            ->join('fund_managers as m1', 'f1.name', '=', 'm1.name')
            ->select('f1.name as fund_name', 'f1.id as fund_id', 'm1.name as manager_name', 'm1.id as manager_id', 'f1.aliases as fund_aliases');
    }

    private function generateCrossJoinWithAliasesSubQuery()
    {
        return DB::table('funds as f2')
            ->crossJoin(DB::raw('(SELECT 0 AS number UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                      UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as numbers'))
            ->join('fund_managers as m2', DB::raw('JSON_UNQUOTE(JSON_EXTRACT(f2.aliases, CONCAT(\'$[\', numbers.number, \']\')))'), '=', 'm2.name')
            ->select('f2.name as fund_name', 'f2.id as fund_id', DB::raw('JSON_UNQUOTE(JSON_EXTRACT(f2.aliases, CONCAT(\'$[\', numbers.number, \']\'))) as manager_name'), 'm2.id as manager_id', 'f2.aliases as fund_aliases');
    }

}
