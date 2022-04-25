/* eslint-env jest */
/* @flow */
import React from 'react';
import { shallow } from 'enzyme';
import { intlMock, $refType, $fragmentRefs } from '../../../mocks';
import { UserAdminPageTabs } from './UserAdminPageTabs';

describe('<UserAdminPageTabs/>', () => {
  const props1 = {
    intl: intlMock,
  };

  const user = {
    $refType,
    $fragmentRefs,
    url: 'https://fr.wikipedia.org/wiki/Dahu',
  };

  const viewer = {
    $refType,
    $fragmentRefs,
  };

  it('should render', () => {
    const wrapper = shallow(<UserAdminPageTabs {...props1} user={user} viewer={viewer} />);
    expect(wrapper).toMatchSnapshot();
  });
});
