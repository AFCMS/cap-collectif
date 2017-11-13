/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';

import IdeaPageComments from './IdeaPageComments';

const props = {
  id: 'idea1',
};

describe('<IdeaPageComments />', () => {
  it('it should render a comment section in a div', () => {
    const wrapper = shallow(<IdeaPageComments {...props} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('it should a div with provided class name', () => {
    const wrapper = shallow(<IdeaPageComments {...props} className="css-class" />);
    expect(wrapper).toMatchSnapshot();
  });
});
