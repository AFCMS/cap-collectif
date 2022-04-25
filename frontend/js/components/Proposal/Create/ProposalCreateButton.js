// @flow
import React from 'react';
import { FormattedMessage } from 'react-intl';
import { Button } from 'react-bootstrap';
import { createFragmentContainer, graphql } from 'react-relay';
import LoginOverlay from '../../Utils/LoginOverlay';
import type { ProposalCreateButton_proposalForm } from '~relay/ProposalCreateButton_proposalForm.graphql';
import { getProposalLabelByType } from '~/utils/interpellationLabelHelper';

type Props = {
  handleClick: Function,
  disabled: boolean,
  proposalForm: ProposalCreateButton_proposalForm,
  projectType: string,
};

export class ProposalCreateButton extends React.Component<Props> {
  render() {
    const { disabled, handleClick, proposalForm, projectType } = this.props;
    const buttonTradKey =
      proposalForm.objectType === 'ESTABLISHMENT'
        ? getProposalLabelByType(projectType, 'add-establishment')
        : proposalForm.objectType === 'PROPOSAL'
        ? getProposalLabelByType(projectType, 'add')
        : 'submit-a-question';
    return (
      <LoginOverlay>
        <Button
          id="add-proposal"
          disabled={disabled}
          bsStyle="primary"
          onClick={disabled ? null : handleClick}>
          <i className="cap cap-add-1" />
          <FormattedMessage id={buttonTradKey} />
        </Button>
      </LoginOverlay>
    );
  }
}

export default createFragmentContainer(ProposalCreateButton, {
  proposalForm: graphql`
    fragment ProposalCreateButton_proposalForm on ProposalForm {
      objectType
    }
  `,
});
