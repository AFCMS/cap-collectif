// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { UnpublishedLabel } from './UnpublishedLabel';
import { $refType } from '../../mocks';

describe('<UnpublishedLabel />', () => {
  it('renders when published', () => {
    const publishablePublished = {
      $refType,
      id: 'id1',
      published: true,
      publishableUntil: null,
      notPublishedReason: null,
    };
    const wrapper = shallow(<UnpublishedLabel publishable={publishablePublished} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders when not published because author must confirm', () => {
    const publishableUnublishedWaiting = {
      $refType,
      id: 'id1',
      published: false,
      publishableUntil: null,
      notPublishedReason: 'WAITING_AUTHOR_CONFIRMATION',
    };
    const wrapper = shallow(<UnpublishedLabel publishable={publishableUnublishedWaiting} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders when not published because author is not confirmed', () => {
    const publishableUnublishedNotConfirmed = {
      $refType,
      id: 'id1',
      published: false,
      publishableUntil: null,
      notPublishedReason: 'AUTHOR_NOT_CONFIRMED',
    };
    const wrapper = shallow(<UnpublishedLabel publishable={publishableUnublishedNotConfirmed} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders when not published because author confirmed his account too late', () => {
    const publishableUnublishedTooLate = {
      $refType,
      id: 'id1',
      published: false,
      publishableUntil: null,
      notPublishedReason: 'AUTHOR_CONFIRMED_TOO_LATE',
    };
    const wrapper = shallow(<UnpublishedLabel publishable={publishableUnublishedTooLate} />);
    expect(wrapper).toMatchSnapshot();
  });
});
