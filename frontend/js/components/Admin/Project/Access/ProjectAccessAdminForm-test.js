// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { ProjectAccessAdminForm } from './ProjectAccessAdminForm';
import { formMock, intlMock, $refType } from '~/mocks';

describe('<ProjectAccessAdminForm />', () => {
  const defaultProps = {
    ...formMock,
    intl: intlMock,
    project: null,
    visibility: 'ADMIN',
    formName: 'ProjectAdminForm',
  };

  it('renders correctly empty', () => {
    const wrapper = shallow(<ProjectAccessAdminForm {...defaultProps} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders correctly with a project', () => {
    const props = {
      ...defaultProps,
      project: {
        $refType,
        visibility: 'ADMIN',
      },
    };
    const wrapper = shallow(<ProjectAccessAdminForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
