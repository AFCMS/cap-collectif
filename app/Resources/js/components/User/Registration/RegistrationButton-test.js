/* eslint-env jest */
/* eslint no-unused-expressions:0 */
import React from 'react';

import { shallow } from 'enzyme';
import { RegistrationButton } from './RegistrationButton';
import IntlData from '../../../translations/FR';

describe('<RegistrationButton />', () => {
  const props = {
    ...IntlData,
  };

  const style = { marginTop: '0' };

  it('renders nothing if registration is not activate', () => {
    const wrapper = shallow(<RegistrationButton features={{ registration: false }} {...props} />);
    expect(wrapper.children()).toHaveLength(0);
  });

  it('renders a button if registration is active', () => {
    const wrapper = shallow(<RegistrationButton features={{ registration: true }} {...props} />);
    const span = wrapper.find('span');
    expect(span).toHaveLength(1);
    const button = span.find('Button');
    expect(button).toHaveLength(1);
    expect(button.prop('bsStyle')).toEqual('primary');
    expect(button.prop('className')).toEqual('navbar-btn btn--registration ');
    expect(button.prop('onClick')).toBeDefined();
  });

  it('renders specified className on button', () => {
    const wrapper = shallow(<RegistrationButton features={{ registration: true }} className="css-class" {...props} />);
    expect(wrapper.find('Button').prop('className')).toEqual('navbar-btn btn--registration css-class');
  });

  it('renders specified style on wrapper', () => {
    const wrapper = shallow(<RegistrationButton features={{ registration: true }} style={style} {...props} />);
    expect(wrapper.find('span').prop('style')).toEqual(style);
    expect(wrapper.find('Button').prop('style')).toEqual({});
  });

  it('renders specified button style on button', () => {
    const wrapper = shallow(<RegistrationButton features={{ registration: true }} buttonStyle={style} {...props} />);
    expect(wrapper.find('span').prop('style')).toEqual({});
    expect(wrapper.find('Button').prop('style')).toEqual(style);
  });
});
