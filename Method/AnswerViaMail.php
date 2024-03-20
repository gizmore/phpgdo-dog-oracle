<?php
namespace GDO\DogOracle\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDT;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_Response;
use GDO\Core\GDT_UInt;
use GDO\DogOracle\GDT_OracleChoices;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Mail\GDT_MailAuth;
use GDO\Poll\GDO_Poll;
use GDO\Poll\Method\View;
use GDO\UI\GDT_Repeat;
use GDO\User\GDO_User;

final class AnswerViaMail extends MethodForm
{

    public function isUserRequired(): bool
    {
        return false;
    }

    public function gdoParameters(): array
    {
        return [
            GDT_MailAuth::make('mx_auth'),
            GDT_Object::make('id')->table(GDO_Poll::table())->notNull(),
        ];
    }

    /**
     * @throws GDO_ArgError
     */
    private function getPoll(): GDO_Poll
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

    /**
     * @throws GDO_ArgError
     */
    public function execute(): GDT
    {
        $this->gdoParameterVar('mxauth');
        $card = View::make()->executeWithInputs($this->getInputs());
        $res = GDT_Response::make();
        $res->addField($card);
        if (GDO_User::current()->isAuthenticated())
        {
            $res->addField(parent::execute());
        }
        return $res;
    }

    public function formValidated(GDT_Form $form): GDT
    {
        return $this->message('msg_voted');
    }

}
