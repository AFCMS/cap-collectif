/* eslint-env mocha */
/* eslint no-unused-expressions:0 */
import React from 'react';
import { expect } from 'chai';
import { shallow } from 'enzyme';
import IntlData from '../../../translations/FR';
import IdeaSidebar from './IdeaSidebar';

const ideaContribuable = {
  canContribute: true,
};

const ideaNotContribuable = {
  canContribute: false,
};

const props = {
  onToggleExpand: (value) => {},
};

describe('<IdeaSidebar />', () => {
  it('should render nothing when idea is not contribuable', () => {
    const wrapper = shallow(<IdeaSidebar idea={ideaNotContribuable} expanded={false} {...props} {...IntlData} />);
    expect(wrapper.children()).to.have.length(0);
  });

  it('should render the sidebar when idea is contribuable', () => {
    const wrapper = shallow(<IdeaSidebar idea={ideaContribuable} expanded={false} {...props} {...IntlData} />);
    expect(wrapper.find('#sidebar')).to.have.length(1);
    expect(wrapper.find('.sidebar-hideable')).to.have.length(2);
    expect(wrapper.find('.sidebar-hidden-small')).to.have.length(1);
    expect(wrapper.find('IdeaVoteBox')).to.have.length(1);
    expect(wrapper.find('Button')).to.have.length(1);
  });

  it('should render expanded class when required', () => {
    const wrapper = shallow(<IdeaSidebar idea={ideaContribuable} expanded={true} {...props} {...IntlData} />);
    expect(wrapper.find('.sidebar-hideable')).to.have.length(2);
    expect(wrapper.find('.sidebar-hidden-small')).to.have.length(0);
  });
});
