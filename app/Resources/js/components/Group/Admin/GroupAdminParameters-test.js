// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { GroupAdminParameters } from './GroupAdminParameters';

describe('<GroupAdminParameters />', () => {
  const props = {
    group: {
      id: 'group4',
      title: 'Comité de suvi',
      description: 'Lorem ipsum dolor sit amet sapien estiam',
    },
    submitting: false,
    submit: () => {},
    invalid: false,
    pristine: false,
  };

  it('render correctly', () => {
    const wrapper = shallow(<GroupAdminParameters {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
