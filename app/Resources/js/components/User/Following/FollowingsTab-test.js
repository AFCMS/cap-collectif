/* eslint-env jest */
// @flow
import React from 'react';
import { shallow } from 'enzyme';
import { FollowingsTab } from './FollowingsTab';
import { $refType } from '../../../mocks';

describe('<FollowingsTab />', () => {
  const viewer = {
    $refType,
    followingOpinions: {
      edges: [
        {
          node: {
            show_url: 'http://carte.perdu.com',
            id: 'opinion1',
            title: "Une carte de l'internet",
            project: {
              id: 'project1',
            },
          },
        },
        {
          node: {
            show_url: 'http://gps.perdu.com',
            id: 'opinion2',
            title: "Un GPS de l'internet",
            project: {
              id: 'project1',
            },
          },
        },
        {
          node: {
            show_url: 'https://randomstreetview.com/',
            id: 'opinion3',
            title: 'Go  nowhere',
            project: {
              id: 'project2',
            },
          },
        },
      ],
    },
    followingProposals: {
      edges: [
        {
          node: {
            show_url: 'http://carte.perdu.com',
            id: 'proposal1',
            title: "Une carte de l'internet",
            project: {
              id: 'project1',
            },
          },
        },
        {
          node: {
            show_url: 'http://gps.perdu.com',
            id: 'proposal2',
            title: "Un GPS de l'internet",
            project: {
              id: 'project1',
            },
          },
        },
        {
          node: {
            show_url: 'https://randomstreetview.com/',
            id: 'proposal3',
            title: 'Go  nowhere',
            project: {
              id: 'project2',
            },
          },
        },
      ],
    },
  };

  it('should render a list of contribution', () => {
    const wrapper = shallow(<FollowingsTab viewer={viewer} />);
    wrapper.setState({ open: true });
    expect(wrapper).toMatchSnapshot();
  });
  it('should render an empty list', () => {
    const wrapper = shallow(
      <FollowingsTab
        viewer={{ followingOpinions: { edges: [] }, followingProposals: { edges: [] } }}
      />,
    );
    wrapper.setState({ open: false });
    expect(wrapper).toMatchSnapshot();
  });
});
