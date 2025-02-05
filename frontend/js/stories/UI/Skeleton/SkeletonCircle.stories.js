// @flow
import * as React from 'react';
import SkeletonCircle, { type SkeletonCircleProps } from '~ds/Skeleton/circle';

export default {
  title: 'Design system/Skeleton/SkeletonCircle',
  component: SkeletonCircle,
  argTypes: {
    size: {
      control: { type: 'text', required: true },
      defaultValue: '50px',
    },
  },
};

const Template = ({ size }: SkeletonCircleProps) => <SkeletonCircle size={size} />;

export const main = Template.bind({});
main.storyName = 'Default';
main.args = {};
