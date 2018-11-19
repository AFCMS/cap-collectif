/* eslint-env jest */
// @flow
import React from 'react';
import { shallow } from 'enzyme';
import { ProposalPageHeader } from './ProposalPageHeader';
import { $refType, $fragmentRefs } from '../../../mocks';

describe('<ProposalPageHeader />', () => {
  const proposal = {
    $refType,
    $fragmentRefs,
    id: '1',
    title: 'titre',
    theme: { title: 'titre du theme' },
    author: { username: 'userAdmin', displayName: 'userAdmin', media: { url: 'http://media.url' } },
    publishedAt: '2015-01-01 00:00:00',
    createdAt: '2015-01-01 00:00:00',
    updatedAt: '2015-01-05 00:00:00',
    publicationStatus: 'PUBLISHED',
    show_url: 'true',
    project: { opinionCanBeFollowed: true },
    form: {
      isProposalForm: true,
    },
  };

  const proposalWithoutTheme = {
    $refType,
    $fragmentRefs,
    id: '1',
    title: 'titre',
    theme: null,
    author: { username: 'userAdmin', displayName: 'userAdmin', media: { url: 'http://media.url' } },
    createdAt: '2015-01-01 00:00:00',
    publishedAt: '2015-01-01 00:00:00',
    updatedAt: '2015-01-05 00:00:00',
    publicationStatus: 'PUBLISHED',
    show_url: 'true',
    project: { opinionCanBeFollowed: true },
    form: {
      isProposalForm: false,
    },
  };

  const props = {
    className: '',
    referer: 'http://capco.test',
  };

  it('should render a proposal header as ProposalFrom', () => {
    const wrapper = shallow(
      <ProposalPageHeader step={null} proposal={proposal} viewer={null} {...props} />,
    );
    expect(wrapper).toMatchSnapshot();
  });

  it('should not render theme if proposal has none and form type is question', () => {
    const wrapper = shallow(
      <ProposalPageHeader proposal={proposalWithoutTheme} step={null} viewer={null} {...props} />,
    );
    expect(wrapper).toMatchSnapshot();
  });
});
