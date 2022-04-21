/* eslint-env jest */
/* eslint no-unused-expressions:0 */
import React from 'react';

import { shallow } from 'enzyme';
import StepPageHeader from './StepPageHeader';

describe('<StepPageHeader />', () => {
  const step = {
    title: '',
    counters: {},
    body: '',
  };

  it('should render a title and a StepInfos', () => {
    const wrapper = shallow(<StepPageHeader step={step} />);
    expect(wrapper.find('h2')).toHaveLength(1);
    expect(wrapper.find('StepInfos')).toHaveLength(1);
  });
});
