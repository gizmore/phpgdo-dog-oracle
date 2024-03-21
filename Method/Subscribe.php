<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDT;
use GDO\Core\GDT_Checkbox;
use GDO\Core\Method;

final class Subscribe extends Method
{

    public function isUserRequired(): bool
    {
        return true;
    }

    public function gdoParameters(): array
    {
        return [
            GDT_Checkbox::make('enable')->notNull(),
        ];
    }

    /**
     * @throws GDO_ArgError
     */
    public function wantEnable(): bool
    {
        return $this->gdoParameterValue('enable');
    }

    public function execute(): GDT
    {

    }

}
