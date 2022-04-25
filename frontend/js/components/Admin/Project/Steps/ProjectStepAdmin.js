// @flow
import React, { useState } from 'react';
import { FieldArray } from 'redux-form';
import { Button, ButtonToolbar } from 'react-bootstrap';
import { FormattedMessage } from 'react-intl';

import ProjectStepAdminList from './ProjectStepAdminList';
import ProjectAdminStepFormModal from '../Step/ProjectAdminStepFormModal';
import { ProjectBoxHeader } from '../Form/ProjectAdminForm.style';

type Props = {|
  form: string,
|};

export default function ProjectStepAdmin(props: Props) {
  const [showAddStepModal, displayAddStepModal] = useState(false);

  const { form } = props;

  return (
    <div className="col-md-12">
      <div className="box box-primary container-fluid">
        <ProjectBoxHeader>
          <h4>
            <FormattedMessage id="project.show.meta.step.title" />
          </h4>
        </ProjectBoxHeader>
        <div className="box-content">
          <div className="form-group" id="proposal_form_admin_questions_panel_personal">
            <FieldArray name="steps" component={ProjectStepAdminList} formName={form} />
            <ButtonToolbar>
              <ProjectAdminStepFormModal
                onClose={() => displayAddStepModal(false)}
                step={null}
                show={showAddStepModal}
                form={form}
              />
              <Button
                id="js-btn-create-question"
                bsStyle="primary"
                className="btn-outline-primary box-content__toolbar"
                onClick={() => displayAddStepModal(true)}>
                <i className="cap cap-bubble-add-2" /> <FormattedMessage id="global.add" />
              </Button>
            </ButtonToolbar>
          </div>
        </div>
      </div>
    </div>
  );
}
