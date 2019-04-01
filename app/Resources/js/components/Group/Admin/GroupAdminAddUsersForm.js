// @flow
import React from 'react';
import { injectIntl, type IntlShape } from 'react-intl';
import { reduxForm } from 'redux-form';
import type { Dispatch } from '../../../types';
import GroupAdminUsers_group from '~relay/GroupAdminUsers_group.graphql';
import { groupAdminUsersUserDeletionReset } from '../../../redux/modules/user';
import AddUsersInGroupMutation from '../../../mutations/AddUsersInGroupMutation';
import UserListField from '../../Admin/Field/UserListField';

type Props = {
  group: GroupAdminUsers_group,
  handleSubmit: Function,
  // eslint-disable-next-line react/no-unused-prop-types
  dispatch: Dispatch,
  // eslint-disable-next-line react/no-unused-prop-types
  onClose: Function,
  intl: IntlShape,
};

type DefaultProps = void;
type FormValues = {
  users: Array<Object>,
};

export const formName = 'group-users-add';

const onSubmit = (values: FormValues, dispatch: Dispatch, { group, onClose, reset }) => {
  const users = [];

  dispatch(groupAdminUsersUserDeletionReset());

  values.users.map(user => {
    users.push(user.value);
  });

  const variables = {
    input: {
      users,
      groupId: group.id,
    },
  };

  return AddUsersInGroupMutation.commit(variables).then(() => {
    reset();
    onClose();
  });
};

export class GroupAdminAddUsersForm extends React.Component<Props> {
  static defaultProps: DefaultProps;

  render() {
    const { handleSubmit, group, intl } = this.props;

    const usersInGroup = [];
    group.users.edges.map(edge => {
      usersInGroup.push(edge.node.id);
    });

    return (
      <form onSubmit={handleSubmit}>
        <div>
          <UserListField
            id="group-users-users"
            name="users"
            label={intl.formatMessage({ id: 'group.admin.form.users' })}
            labelClassName="control-label"
            inputClassName="fake-inputClassName"
            placeholder="Sélectionnez un utilisateur"
            userListToNoSearch={usersInGroup}
          />
        </div>
      </form>
    );
  }
}

const form = reduxForm({
  onSubmit,
  form: formName,
  destroyOnUnmount: false,
})(GroupAdminAddUsersForm);

export default injectIntl(form);
