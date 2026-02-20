<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Produk';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'edit') {
                                    $set('slug', Product::generateSlug($state));
                                }
                            }),
                        
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Slug akan digunakan untuk URL produk'),
                        
                        Select::make('category')
                            ->label('Kategori')
                            ->required()
                            ->options([
                                'Semen' => 'Semen',
                                'Besi' => 'Besi',
                                'Pasir' => 'Pasir',
                                'Batu' => 'Batu',
                                'Kayu' => 'Kayu',
                                'Keramik' => 'Keramik',
                                'Plesteran' => 'Plesteran',
                                'Lantai' => 'Lantai',
                                'Dinding' => 'Dinding',
                                'Atap' => 'Atap',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->searchable()
                            ->preload(),
                        
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Deskripsi singkat tentang produk'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Harga & Stok')
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0)
                            ->step(1000)
                            ->helperText('Harga per unit produk'),
                        
                        TextInput::make('stock')
                            ->label('Stok')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->helperText('Jumlah stok yang tersedia'),
                        
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50)
                            ->default('sak')
                            ->helperText('Contoh: sak, ton, meter, lembar, dll'),
                        
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->helperText('Apakah produk ini tersedia untuk dijual?'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Gambar Produk')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Gambar')
                            ->directory('products')
                            ->preserveFilenames()
                            ->image()
                            ->imagePreviewHeight(200)
                            ->required()
                            ->helperText('Format gambar yang didukung: JPG, PNG, WebP'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->width(80)
                    ->height(80)
                    ->rounded(),
                
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Semen' => 'danger',
                        'Besi' => 'warning',
                        'Pasir' => 'info',
                        'Batu' => 'success',
                        'Kayu' => 'primary',
                        default => 'gray',
                    }),
                
                TextColumn::make('price')
                    ->label('Harga')
                    ->sortable()
                    ->money('IDR')
                    ->alignRight(),
                
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
                
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable()
                    ->alignCenter(),
                
                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->sortable()
                    ->alignCenter(),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenBy: true),
                
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenBy: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Semen' => 'Semen',
                        'Besi' => 'Besi',
                        'Pasir' => 'Pasir',
                        'Batu' => 'Batu',
                        'Kayu' => 'Kayu',
                        'Keramik' => 'Keramik',
                        'Plesteran' => 'Plesteran',
                        'Lantai' => 'Lantai',
                        'Dinding' => 'Dinding',
                        'Atap' => 'Atap',
                        'Lainnya' => 'Lainnya',
                    ])
                    ->multiple()
                    ->searchable(),
                
                Filter::make('active_products')
                    ->label('Produk Aktif')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
                
                Filter::make('in_stock')
                    ->label('Stok Tersedia')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '>', 0)),
                
                Filter::make('price_range')
                    ->label('Rentang Harga')
                    ->form([
                        TextInput::make('min_price')
                            ->label('Harga Minimum')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('max_price')
                            ->label('Harga Maximum')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                function (Builder $query, $minPrice): Builder {
                                    return $query->where('price', '>=', $minPrice);
                                },
                            )
                            ->when(
                                $data['max_price'],
                                function (Builder $query, $maxPrice): Builder {
                                    return $query->where('price', '<=', $maxPrice);
                                },
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate_products')
                        ->label('Aktifkan Produk')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                            }
                        }),
                    Tables\Actions\BulkAction::make('deactivate_products')
                        ->label('Nonaktifkan Produk')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                            }
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::active()->count() > 0 ? 'success' : 'danger';
    }
}
