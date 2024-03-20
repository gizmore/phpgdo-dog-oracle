<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDT_Object;
use GDO\DogOracle\GDT_OracleChoices;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Poll\GDO_Poll;

/**
 * Answer via chat connectors.
 */
final class AnswerViaChat extends MethodForm
{

    public function isCLI(): bool
    {
        return true;
    }

    public function getCLITrigger(): string
    {
        return 'vote';
    }

    public function gdoParameters(): array
    {
        return [
            GDT_Object::make('id')->table(GDO_Poll::table())->notNull(),
        ];
    }

    /**
     * @throws GDO_ArgError
     */
    public function getPoll(): GDO_Poll
    {
        return $this->gdoParameterValue('id');
    }

    /**
     * @throws GDO_ArgError
     */
    protected function createForm(GDT_Form $form): void
    {
        $poll = $this->getPoll();
        $form->addFields(
            GDT_OracleChoices::make('answer')->poll($poll),
        );
        $form->actions()->addField(GDT_Submit::make());
    }

}
