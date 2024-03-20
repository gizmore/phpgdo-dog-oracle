<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDT;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_UInt;
use GDO\DogOracle\GDT_OracleChoices;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Poll\GDO_Poll;
use GDO\UI\GDT_Repeat;

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
            GDT_Repeat::makeAs('answer', GDT_UInt::make()),
        ];
    }

    /**
     * @throws GDO_ArgError
     */
    public function getPoll(): ?GDO_Poll
    {
        return $this->gdoParameterValue('id');
    }

    /**
     * @throws GDO_ArgError
     */
    protected function createForm(GDT_Form $form): void
    {
        if ($poll = $this->getPoll())
        {
            $form->addFields(
                GDT_OracleChoices::make('answer')->poll($poll),
            );
            $form->actions()->addField(GDT_Submit::make());
        }
    }

    public function formValidated(GDT_Form $form): GDT
    {
        return $this->message('msg_dog_voted');
    }

}
