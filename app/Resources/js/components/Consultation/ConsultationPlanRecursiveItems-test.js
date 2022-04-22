// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { ConsultationPlanRecursiveItems } from './ConsultationPlanRecursiveItems';
import { $refType, $fragmentRefs } from '../../mocks';

describe('<ConsultationPlanRecursiveItems />', () => {
  const props = {
    consultation: {
      $refType,
      sections: [{ sections: [], $fragmentRefs }, { sections: [], $fragmentRefs }],
    },
    stepId: "myStep",
    closePlan: jest.fn(),
    openPlan: jest.fn(),
    showConsultationPlan: true,
  };

  it('renders correcty open plan', () => {
    const wrapper = shallow(<ConsultationPlanRecursiveItems {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders correcty close plan', () => {
    const wrapper = shallow(<ConsultationPlanRecursiveItems {...props} />);
    wrapper.setProps({ showConsultationPlan: false });
    expect(wrapper).toMatchSnapshot();
  });
});
