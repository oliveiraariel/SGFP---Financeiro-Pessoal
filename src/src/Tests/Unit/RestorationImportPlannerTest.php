<?php
declare(strict_types=1);
namespace SGFP\Tests\Unit;
use PHPUnit\Framework\TestCase;
use SGFP\Application\Backup\{RestorationImportPlanner,StagedBackupDecoder};
final class RestorationImportPlannerTest extends TestCase
{
    public function testPlansOrderAndRemapsNullableReferences(): void
    {
        $p=['metadata'=>['schema'=>'sgfp-backup','version'=>1,'owner'=>7,'origin'=>'manual','created_at'=>'x'],'theme'=>'dark','accounts'=>[['id'=>10,'name'=>'A','role'=>'PRINCIPAL']],'categories'=>[['id'=>20,'name'=>'C']],'recurrences'=>[],'commitments'=>[['id'=>30,'categoryId'=>20,'recurrenceId'=>null,'name'=>'X','type'=>'PADRAO','nature'=>'SAIDA','status'=>'PENDENTE']],'transfers'=>[],'entries'=>[['id'=>40,'accountId'=>10,'commitmentId'=>null,'origin'=>'SALDO_INICIAL','effectType'=>'ENTRADA','state'=>'ATIVO']]];
        $plan=(new RestorationImportPlanner())->plan((new StagedBackupDecoder())->decode($p,7),7);
        self::assertSame(['accounts','categories','recurrences','commitments','transfers','entries','theme'],$plan->replacementOrder);
        self::assertSame('categories:20',$plan->records['commitments'][0]['categoryId']);
        self::assertNull($plan->records['commitments'][0]['recurrenceId']);
        self::assertSame('accounts:10',$plan->records['entries'][0]['accountId']);
    }
}
