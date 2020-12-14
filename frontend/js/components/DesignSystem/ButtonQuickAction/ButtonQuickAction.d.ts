import {PolymorphicComponent} from '../../Ui/Primitives/AppBox';
import { ComponentProps } from 'react';

declare const ButtonQuickAction: PolymorphicComponent<ComponentProps<"button"> & {
    icon: string,
    label: string,
    variantColor?: 'primary' | 'danger' | 'green',
}>

export default ButtonQuickAction
