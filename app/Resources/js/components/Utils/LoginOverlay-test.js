/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { LoginOverlay } from './LoginOverlay';
import IntlData from '../../translations/FR';

describe('<LoginOverlay />', () => {
  const props = {
    ...IntlData,
    openLoginModal: jest.fn(),
    isLoginOrRegistrationModalOpen: false,
    showRegistrationButton: false,
    openRegistrationModal: jest.fn(),
  };

  it('renders children if not enabled', () => {
    const wrapper = shallow(
      <LoginOverlay enabled={false} showRegistrationButton {...props}>
        <div className="foo" />
      </LoginOverlay>,
    );
    expect(wrapper.html()).toEqual('<div class="foo"></div>');
  });

  it('renders children if user is logged', () => {
    const wrapper = shallow(
      <LoginOverlay enabled user={{}} showRegistrationButton {...props}>
        <div className="foo" />
      </LoginOverlay>,
    );
    expect(wrapper.html()).toEqual('<div class="foo"></div>');
  });

  it('renders popover if user is not logged', () => {
    const wrapper = shallow(
      <LoginOverlay enabled user={null} showRegistrationButton {...props}>
        <div className="foo" />
      </LoginOverlay>,
    );
    expect(wrapper.find('OverlayTrigger')).toHaveLength(1);
    expect(wrapper.find('OverlayTrigger').html()).toEqual('<div class="foo" aria-describedby="login-popover"></div>');
  });
});
