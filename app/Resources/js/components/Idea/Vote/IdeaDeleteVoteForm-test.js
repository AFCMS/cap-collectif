/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';

import { IdeaDeleteVoteForm } from './IdeaDeleteVoteForm';

const props = {
  dispatch: () => {},
  idea: {},
  isSubmitting: false,
  onSubmitSuccess: () => {},
  onFailure: () => {},
  anonymous: false,
};

describe('<IdeaDeleteVoteForm />', () => {
  it('should render the idea vote form', () => {
    const wrapper = shallow(<IdeaDeleteVoteForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
