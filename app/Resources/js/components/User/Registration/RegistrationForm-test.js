// @flow
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { RegistrationForm } from './RegistrationForm';
import IntlData from '../../../translations/FR';

describe('<RegistrationForm />', () => {
  const props = {
    ...IntlData,
    addUserTypeField: false,
    addZipcodeField: false,
    addCaptchaField: true,
    handleSubmit: jest.fn(),
    userTypes: [],
    cguName: 'la charte',
    cguLink: '/charte',
    dynamicFields: [],
  };

  it('renders a form with inputs and a captcha', () => {
    const wrapper = shallow(<RegistrationForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders a form with user_type select if enabled', () => {
    const wrapper = shallow(
      <RegistrationForm
        {...props}
        userTypes={[{ id: 1, name: 'type_1' }]}
        addUserTypeField
      />,
    );
    expect(wrapper).toMatchSnapshot();
  });

  it('renders a form with zipcode if enabled', () => {
    const wrapper = shallow(<RegistrationForm {...props} addZipcodeField />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders a form with dynamic fields', () => {
    const wrapper = shallow(
      <RegistrationForm
        {...props}
        dynamicFields={[
          {
            type: 'text',
            required: true,
            private: false,
            question: 'Champ pas facultatif',
            slug: 'champ-pas-facultatif',
            id: 6,
          },
        ]}
      />,
    );
    expect(wrapper).toMatchSnapshot();
  });
});
