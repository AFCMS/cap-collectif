// @flow
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { DeleteAccountModal } from './DeleteAccountModal';

const mockRefType: any = null;

describe('<DeleteAccountModal />', () => {
  const viewer = {
    $refType: mockRefType,
    contributionsCount: 20,
    votesCount: 10,
    contributionsToDeleteCount: 5,
  };

  it('should render an visible modal', () => {
    const wrapper = shallow(<DeleteAccountModal show viewer={viewer} handleClose={() => {}} />);
    expect(wrapper).toMatchSnapshot();
  });
  it('should render an hidden modal', () => {
    const wrapper = shallow(
      <DeleteAccountModal show={false} viewer={viewer} handleClose={() => {}} />,
    );
    expect(wrapper).toMatchSnapshot();
  });
});
