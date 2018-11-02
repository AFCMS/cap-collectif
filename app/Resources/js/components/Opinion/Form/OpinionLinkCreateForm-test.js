// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { OpinionLinkCreateForm } from './OpinionLinkCreateForm';

describe('<OpinionLinkCreateForm />', () => {
  const availableTypes = [{ id: '1337', appendixTypes: [{ id: '1', title: 'appendix-1' }] }];
  const props = {
    availableTypes,
    currentType: availableTypes[0],
    handleSubmit: jest.fn(),
    opinion: {},
    initialValues: {
      opinionType: '12',
    },
  };

  it('renders correctly', () => {
    const wrapper = shallow(<OpinionLinkCreateForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
