// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { ProposalCollectStatus } from './ProposalCollectStatus';

describe('<ProposalCollectStatus />', () => {
  const proposalWithoutStatus = {
    proposal: {
      status: null,
    },
  };
  const proposalWithStatus = {
    proposal: {
      status: { name: 'Terminé', color: 'success' },
    },
  };

  it('renders proposal without status', () => {
    const wrapper = shallow(<ProposalCollectStatus {...proposalWithoutStatus} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders proposal with status', () => {
    const wrapper = shallow(<ProposalCollectStatus {...proposalWithStatus} />);
    expect(wrapper).toMatchSnapshot();
  });
});
