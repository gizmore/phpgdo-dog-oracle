<?php
namespace GDO\DogOracle;

use GDO\Core\GDO;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_Text;

final class DOG_OracleAsk extends GDO
{

    public function gdoColumns(): array
    {
        return [
            GDT_AutoInc::make('do_id'),
            GDT_Text::make('do_question'),
            GDT_CreatedAt::make('do_created'),
            GDT_CreatedBy::make('do_creator'),
        ];
    }

}
