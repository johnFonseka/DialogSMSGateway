<?php 
namespace Ef\SmsGateway\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/***

+-----------+--------------+------+-----+---------+----------------+
| Field     | Type         | Null | Key | Default | Extra          |
+-----------+--------------+------+-----+---------+----------------+
| entity_id | int unsigned | NO   | PRI | NULL    | auto_increment |
| clientref | varchar(20)  | NO   |     | NULL    |                |
| number    | int          | NO   |     | NULL    |                |
| message   | mediumtext   | YES  |     | NULL    |                |
| campaign  | varchar(20)  | YES  |     | MERKADO |                |
| status    | varchar(12)  | YES  |     | PENDING |                |
| tries     | int          | YES  |     | 0       |                |
| created   | datetime     | YES  |     | NULL    |                |
| sent      | datetime     | YES  |     | NULL    |                |
+-----------+--------------+------+-----+---------+----------------+

 */

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup,ModuleContextInterface $context){
        $setup->startSetup();
        $conn = $setup->getConnection();
        $tableName = $setup->getTable('ef_sms_messages');
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                'entity_id',
                                Table::TYPE_INTEGER,
                                null,
                                ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true, 'auto_increment' => true]
                            )->addColumn(
                                'client_id',
                                Table::TYPE_TEXT,
                                '20',
                                ['nullable'=>false]
                            )->addColumn(
                                'number',
                                Table::TYPE_INTEGER,
                                12,
                                ['nullable'=>false]
                            )->addColumn(
                                'message',
                                Table::TYPE_TEXT,
                                '1M',
                                ['nullbale'=>false]
                            )->addColumn(
                                'campaign',
                                Table::TYPE_TEXT,
                                '20',
                                ['nullbale'=>false,'default'=>'MERKADO']
                            )->addColumn(
                                'status',
                                Table::TYPE_TEXT,
                                '12',
                                ['nullbale'=>false,'default'=>'PENDING']
                            )->addColumn(
                                'tries',
                                Table::TYPE_INTEGER,
                                2,
                                ['nullbale'=>true, 'default' => 0]
                            )->addColumn(
                                'created',
                                Table::TYPE_DATETIME,
                                '20',
                                ['nullbale'=>true]
                            )->addColumn(
                                'sent',
                                Table::TYPE_DATETIME,
                                '20',
                                ['nullbale'=>true]
                            )->addColumn(
                                'api_response',
                                Table::TYPE_TEXT,
                                '100',
                                ['nullbale'=>true]
                            )->setOption('charset','utf8');
            $conn->createTable($table);
        }
        $setup->endSetup();
    }
}

?>