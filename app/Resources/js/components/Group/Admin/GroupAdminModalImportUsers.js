// @flow
import * as React from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import type { IntlShape } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { Modal, Button } from 'react-bootstrap';
import { submit, isSubmitting, isPristine } from 'redux-form';
import GroupAdminUsers_group from './__generated__/GroupAdminUsers_group.graphql';
import CloseButton from '../../Form/CloseButton';
import type { Dispatch } from '../../../types';
import GroupAdminImportUsersForm, { formName } from './GroupAdminImportUsersForm';

type Props = {
  show: boolean,
  onClose: Function,
  group: GroupAdminUsers_group,
  dispatch: Dispatch,
  intl: IntlShape,
  submitting: boolean,
  pristine: boolean,
};

export class GroupAdminModalImportUsers extends React.Component<Props> {
  render() {
    const { show, onClose, group, dispatch, intl, submitting, pristine } = this.props;

    return (
      <Modal show={show} onHide={onClose} aria-labelledby="delete-modal-title-lg">
        <Modal.Header>
          <Modal.Title id="contained-modal-title-lg">
            {<FormattedMessage id="import-users" />}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <GroupAdminImportUsersForm group={group} onClose={onClose} />
        </Modal.Body>
        <Modal.Footer>
          <CloseButton label={intl.formatMessage({ id: 'global.close' })} onClose={onClose} />
          <Button
            disabled={pristine || submitting}
            bsStyle="primary"
            type="button"
            onClick={() => dispatch(submit(formName))}>
            <FormattedMessage id="import" />
          </Button>
        </Modal.Footer>
      </Modal>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = state => ({
  submitting: isSubmitting(formName)(state),
  pristine: isPristine(formName)(state),
});

export default connect(mapStateToProps)(injectIntl(GroupAdminModalImportUsers));
