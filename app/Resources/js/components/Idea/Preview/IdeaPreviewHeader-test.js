/* eslint-env jest */
/* eslint no-unused-expressions:0 */
import React from 'react';

import { shallow } from 'enzyme';
import IntlData from '../../../translations/FR';
import IdeaPreviewHeader from './IdeaPreviewHeader';
import UserAvatar from '../../User/UserAvatar';
import UserLink from '../../User/UserLink';

const idea = {
  id: 1,
  author: {},
};

describe('<IdeaPreviewHeader />', () => {
  it('should render idea preview header', () => {
    const wrapper = shallow(<IdeaPreviewHeader idea={idea} {...IntlData} />);
    expect(wrapper.find('.idea__preview__header')).toHaveLength(1);
    expect(wrapper.find('.idea__date')).toHaveLength(1);
    expect(wrapper.find(UserLink)).toHaveLength(1);
    expect(wrapper.find(UserAvatar)).toHaveLength(1);
  });
});
