<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\ValidationRules;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Declaration\ValidationRules\CheckReferenceColumnHasIndex;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Real;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\Schema;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

class CheckReferenceColumnHasIndexTest extends \PHPUnit\Framework\TestCase
{
    /** @var CheckReferenceColumnHasIndex */
    private $model;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            CheckReferenceColumnHasIndex::class,
            [
            ]
        );
    }

    public function testValidate()
    {
        $table = new Table('name', 'name', 'table', 'default', 'innodb');
        $refTable = new Table(
            'ref_table',
            'name',
            'table',
            'default',
            'innodb'
        );

        $column = new Real('decimal', 'decimal', $table, 10, 5);
        $refColumn = new Real('ref_decimal', 'decimal', $refTable, 10, 5);
        $reference = new Reference(
            'ref',
            'foreign',
            $table,
            $column,
            $refTable,
            $refColumn,
            'CASCADE'
        );

        $table->addColumns([$column]);
        $refTable->addColumns([$refColumn]);
        $table->addConstraints([$reference]);
        /** @var Schema|\PHPUnit_Framework_MockObject_MockObject $schemaMock */
        $schemaMock = $this->getMockBuilder(Schema::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaMock->expects(self::once())
            ->method('getTables')
            ->willReturn([$table]);
        self::assertEquals(
            [
                [
                        'column' => 'ref_decimal',
                        'message' => 'Reference column ref_decimal in reference table ref_table do not have index',
                ],
            ],
            $this->model->validate($schemaMock)
        );
    }
}
