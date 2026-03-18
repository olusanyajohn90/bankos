<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SanctionsListSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if already seeded
        if (DB::table('sanctions_list')->count() > 0) {
            $this->command->info('Sanctions list already seeded, skipping.');
            return;
        }

        $entries = [
            // OFAC — Fictional individuals (Nigerian-style names for fuzzy match testing)
            [
                'list_source'   => 'OFAC',
                'entity_type'   => 'individual',
                'full_name'     => 'ADEWALE BABATUNDE OKONKWO',
                'aliases'       => json_encode(['BABATUNDE OKONKWO', 'A.B. OKONKWO', 'ADEWALE OKONKWO']),
                'date_of_birth' => '1972-03-15',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['passport' => 'B00123456']),
                'programs'      => json_encode(['SDN', 'CYBER2']),
                'remarks'       => 'Fictional test entry. Allegedly involved in cyber-enabled financial fraud.',
                'is_active'     => true,
                'last_updated'  => '2024-01-15',
            ],
            [
                'list_source'   => 'OFAC',
                'entity_type'   => 'individual',
                'full_name'     => 'IBRAHIM MUSA ALIYU',
                'aliases'       => json_encode(['IBRAHIM ALIYU', 'MUSA ALIYU']),
                'date_of_birth' => '1968-07-22',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['passport' => 'A09876543', 'NIN' => 'NIN-00112233']),
                'programs'      => json_encode(['SDGT']),
                'remarks'       => 'Fictional test entry. Designated for terrorism financing.',
                'is_active'     => true,
                'last_updated'  => '2024-02-10',
            ],
            [
                'list_source'   => 'UN',
                'entity_type'   => 'individual',
                'full_name'     => 'CHIDINMA PEACE EZE',
                'aliases'       => json_encode(['PEACE EZE', 'CHIDINMA EZE-OKAFOR']),
                'date_of_birth' => '1985-11-30',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['passport' => 'C44556677']),
                'programs'      => json_encode(['1267']),
                'remarks'       => 'Fictional test entry. UN Security Council listed.',
                'is_active'     => true,
                'last_updated'  => '2023-09-05',
            ],
            [
                'list_source'   => 'EU',
                'entity_type'   => 'individual',
                'full_name'     => 'EMEKA SUNDAY NWOSU',
                'aliases'       => json_encode(['SUNDAY NWOSU', 'EMEKA S. NWOSU']),
                'date_of_birth' => '1975-04-18',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['passport' => 'D55667788']),
                'programs'      => json_encode(['EU-SANCTIONS']),
                'remarks'       => 'Fictional test entry. EU asset freeze and travel ban.',
                'is_active'     => true,
                'last_updated'  => '2024-03-01',
            ],
            [
                'list_source'   => 'CBN',
                'entity_type'   => 'individual',
                'full_name'     => 'FATIMAH BELLO ABDULLAHI',
                'aliases'       => json_encode(['FATIMA ABDULLAHI', 'F.B. ABDULLAHI']),
                'date_of_birth' => '1990-01-05',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['BVN' => 'BVN-99887766']),
                'programs'      => json_encode(['CBN-PEP']),
                'remarks'       => 'Fictional test entry. CBN Politically Exposed Person watch.',
                'is_active'     => true,
                'last_updated'  => '2024-01-20',
            ],
            // OFAC — Fictional entities
            [
                'list_source'   => 'OFAC',
                'entity_type'   => 'entity',
                'full_name'     => 'GOLDEN BRIDGE TRADING LIMITED',
                'aliases'       => json_encode(['GOLDEN BRIDGE TRADING LTD', 'GBT LIMITED', 'GOLDEN BRIDGE NIGERIA']),
                'date_of_birth' => null,
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['RC' => 'RC-1122334', 'TIN' => '0011223344']),
                'programs'      => json_encode(['SDN']),
                'remarks'       => 'Fictional test entity. Front company for money laundering.',
                'is_active'     => true,
                'last_updated'  => '2024-02-28',
            ],
            [
                'list_source'   => 'UN',
                'entity_type'   => 'entity',
                'full_name'     => 'NORTHERN CRESCENT INVESTMENT GROUP',
                'aliases'       => json_encode(['NCIG', 'NORTHERN CRESCENT INVESTMENTS', 'NC INVESTMENT GROUP']),
                'date_of_birth' => null,
                'nationality'   => null,
                'id_numbers'    => null,
                'programs'      => json_encode(['1267', 'ISIL']),
                'remarks'       => 'Fictional test entity. Allegedly channeling funds to listed groups.',
                'is_active'     => true,
                'last_updated'  => '2023-12-11',
            ],
            [
                'list_source'   => 'EU',
                'entity_type'   => 'entity',
                'full_name'     => 'DELTA STAR RESOURCES COMPANY',
                'aliases'       => json_encode(['DELTA STAR RESOURCES', 'DELTA-STAR CO']),
                'date_of_birth' => null,
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['RC' => 'RC-5566778']),
                'programs'      => json_encode(['EU-SANCTIONS', 'WEST-AFRICA']),
                'remarks'       => 'Fictional test entity. Sanctioned for natural resource exploitation.',
                'is_active'     => true,
                'last_updated'  => '2024-01-08',
            ],
            // Fuzzy-match test entries (for development QA)
            [
                'list_source'   => 'CUSTOM',
                'entity_type'   => 'individual',
                'full_name'     => 'OLUWASEUN ADEYEMI JOHNSON',
                'aliases'       => json_encode(['SEUN JOHNSON', 'O.A. JOHNSON']),
                'date_of_birth' => '1988-06-12',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['passport' => 'E77889900']),
                'programs'      => json_encode(['CUSTOM-WATCH']),
                'remarks'       => 'Fictional test entry. Custom watchlist for development fuzzy-match testing.',
                'is_active'     => true,
                'last_updated'  => '2024-03-01',
            ],
            [
                'list_source'   => 'OFAC',
                'entity_type'   => 'individual',
                'full_name'     => 'YAKUBU DANJUMA TANKO',
                'aliases'       => json_encode(['Y.D. TANKO', 'DANJUMA TANKO', 'YAKUBU TANKO']),
                'date_of_birth' => '1963-09-27',
                'nationality'   => 'Nigerian',
                'id_numbers'    => json_encode(['passport' => 'F11223344']),
                'programs'      => json_encode(['SDN', 'AFRICA']),
                'remarks'       => 'Fictional test entry. Levenshtein distance testing — try searching YAKUBU DANJUMA TANKU.',
                'is_active'     => true,
                'last_updated'  => '2023-11-15',
            ],
        ];

        DB::table('sanctions_list')->insert($entries);

        $this->command->info('Seeded ' . count($entries) . ' fictional sanctions list entries.');
    }
}
